<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Customer\UseCase\ListCustomersUseCase;
use App\Application\Order\UseCase\ChangeOrderStatusUseCase;
use App\Application\Order\UseCase\CreateOrderUseCase;
use App\Application\Order\UseCase\ExportOrderCsvUseCase;
use App\Application\Order\UseCase\GetOrderUseCase;
use App\Application\Order\UseCase\ListOrdersUseCase;
use App\Application\Order\UseCase\ListTrashedOrdersUseCase;
use App\Application\Order\UseCase\RenderOrderPdfUseCase;
use App\Application\Order\UseCase\RestoreOrderUseCase;
use App\Application\Order\UseCase\TrashOrderUseCase;
use App\Application\Order\UseCase\UpdateOrderUseCase;
use App\Application\Shared\Output\RenderedDocument;
use App\Domain\Customer\Entity\Customer;
use App\Domain\Order\ValueObject\TaxRate;
use App\Http\Requests\ChangeOrderStatusRequest;
use App\Http\Requests\SaveOrderRequest;
use App\Support\Flash;
use App\Http\ViewModels\CustomerView;
use App\Http\ViewModels\OrderView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * 発注書。
 *
 * 移行前はこのクラスに DB アクセス・金額計算・PDF 描画・CSV 書き出しが
 * すべて入っていた (511 行)。いまはユースケースを呼んで、その結果を
 * ビューかレスポンスに載せるだけ。
 *
 * 画面は Inertia + Svelte に移行済み (resources/ts/Pages/Orders/)。
 * ほかの画面はまだ Blade なので、view() を返すコントローラと混在している。
 *
 * PDF / CSV のダウンロードは Inertia を通さない。Inertia のリクエストは XHR に
 * なるためファイルを受け取れず、素のリンクとして開く必要がある。
 */
final class OrderController extends Controller
{
    public function index(ListOrdersUseCase $listOrders, ListCustomersUseCase $listCustomers): InertiaResponse
    {
        return Inertia::render('Orders/Index', [
            'orders' => OrderView::collection($listOrders->execute(), $this->customerNames($listCustomers->execute())),
            'urls' => [
                'create' => route('orders.create'),
                'trash' => route('orders.trash'),
                'setStatus' => route('orders.setStatus'),
            ],
        ]);
    }

    public function trash(ListTrashedOrdersUseCase $listTrashed, ListCustomersUseCase $listCustomers): InertiaResponse
    {
        return Inertia::render('Orders/Trash', [
            'orders' => OrderView::collection($listTrashed->execute(), $this->customerNames($listCustomers->execute())),
            'urls' => [
                'index' => route('orders.index'),
                'setStatus' => route('orders.setStatus'),
            ],
        ]);
    }

    /** 新規作成フォーム */
    public function create(ListCustomersUseCase $listCustomers): InertiaResponse
    {
        return Inertia::render('Orders/Form', [
            'customers' => CustomerView::options($listCustomers->execute()),
            'order' => null,
            'taxOptions' => $this->taxOptions(),
            'urls' => [
                'submit' => route('orders.create'),
                'back' => route('orders.index'),
            ],
        ]);
    }

    public function store(SaveOrderRequest $request, CreateOrderUseCase $createOrder): RedirectResponse
    {
        $createOrder->execute($request->toInput());

        return redirect()->route('orders.index')->with(Flash::success('発注書を作成しました'));
    }

    /** 編集フォーム */
    public function edit(int $id, GetOrderUseCase $getOrder, ListCustomersUseCase $listCustomers): InertiaResponse
    {
        $order = $getOrder->execute($id);
        $customers = $listCustomers->execute();
        $customerNames = $this->customerNames($customers);

        return Inertia::render('Orders/Form', [
            'customers' => CustomerView::options($customers),
            'order' => OrderView::fromEntity($order, $customerNames[$order->customerId()] ?? ''),
            'taxOptions' => $this->taxOptions(),
            'urls' => [
                'submit' => route('orders.edit', ['id' => $id]),
                'back' => route('orders.index'),
            ],
        ]);
    }

    public function update(SaveOrderRequest $request, int $id, UpdateOrderUseCase $updateOrder): RedirectResponse
    {
        $updateOrder->execute($id, $request->toInput());

        return redirect()->route('orders.index')->with(Flash::success('発注書を更新しました'));
    }

    /** 詳細 */
    public function show(int $id, GetOrderUseCase $getOrder, ListCustomersUseCase $listCustomers): InertiaResponse
    {
        $order = $getOrder->execute($id);
        $customerNames = $this->customerNames($listCustomers->execute());

        return Inertia::render('Orders/Show', [
            'order' => OrderView::fromEntity($order, $customerNames[$order->customerId()] ?? ''),
            'urls' => ['index' => route('orders.index')],
        ]);
    }

    public function pdf(int $id, RenderOrderPdfUseCase $renderPdf): Response
    {
        return $this->download($renderPdf->execute($id), inline: true);
    }

    public function csv(int $id, ExportOrderCsvUseCase $exportCsv): Response
    {
        return $this->download($exportCsv->execute($id));
    }

    public function delete(int $id, TrashOrderUseCase $trashOrder): RedirectResponse
    {
        $trashOrder->execute($id);

        return redirect()->route('orders.index')->with(Flash::success('ごみ箱に入れました'));
    }

    public function restore(int $id, RestoreOrderUseCase $restoreOrder): RedirectResponse
    {
        $restoreOrder->execute($id);

        return redirect()->route('orders.index')->with(Flash::success('ごみ箱から戻しました'));
    }

    /**
     * 一覧のステータスピルからの遷移。
     *
     * 移行前は JSON を返し、フロント (status.ts) が 200 を見て location.reload()
     * していた。Inertia では元のページへリダイレクトすれば、その画面の props
     * だけが取り直されるので全体の再読み込みが要らない。
     */
    public function setStatus(ChangeOrderStatusRequest $request, ChangeOrderStatusUseCase $changeStatus): RedirectResponse
    {
        $changeStatus->execute($request->orderId(), $request->statusKind());

        return back();
    }

    /**
     * 生成済みのファイルをレスポンスに載せる。
     *
     * 移行前は TCPDF の Output() や readfile() が直接書き出していたため、
     * ミドルウェアがレスポンスに触れず、テストからも検証できなかった。
     */
    private function download(RenderedDocument $document, bool $inline = false): Response
    {
        return response($document->contents, 200, [
            'Content-Type' => $document->contentType,
            'Content-Disposition' => sprintf(
                '%s; filename="%s"',
                $inline ? 'inline' : 'attachment',
                $document->fileName,
            ),
        ]);
    }

    /**
     * 税区分の選択肢。Svelte 側で扱いやすいよう {value, label} の配列にする
     * (Blade には value => label のマップで渡していた)。
     *
     * @return list<array{value: int, label: string}>
     */
    private function taxOptions(): array
    {
        $options = [];

        foreach (TaxRate::options() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }

        return $options;
    }

    /**
     * 顧客 ID => 顧客名。一覧で発注書ごとに顧客を引くと N+1 になるため、
     * まとめて引いて対応表にする。
     *
     * @param list<Customer> $customers
     * @return array<int, string>
     */
    private function customerNames(array $customers): array
    {
        $names = [];

        foreach ($customers as $customer) {
            $names[(int) $customer->id()] = $customer->name();
        }

        return $names;
    }
}
