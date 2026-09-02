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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * 発注書。
 *
 * 移行前はこのクラスに DB アクセス・金額計算・PDF 描画・CSV 書き出しが
 * すべて入っていた (511 行)。いまはユースケースを呼んで、その結果を
 * ビューかレスポンスに載せるだけ。
 */
final class OrderController extends Controller
{
    public function index(ListOrdersUseCase $listOrders, ListCustomersUseCase $listCustomers): View
    {
        return view('orders.index', [
            'orders' => OrderView::collection($listOrders->execute(), $this->customerNames($listCustomers->execute())),
        ]);
    }

    public function trash(ListTrashedOrdersUseCase $listTrashed, ListCustomersUseCase $listCustomers): View
    {
        return view('orders.trash', [
            'orders' => OrderView::collection($listTrashed->execute(), $this->customerNames($listCustomers->execute())),
        ]);
    }

    /** 新規作成フォーム */
    public function create(ListCustomersUseCase $listCustomers): View
    {
        return view('orders.form', [
            'customers' => CustomerView::collection($listCustomers->execute()),
            'order' => null,
            'taxOptions' => TaxRate::options(),
        ]);
    }

    public function store(SaveOrderRequest $request, CreateOrderUseCase $createOrder): RedirectResponse
    {
        $createOrder->execute($request->toInput());

        return redirect()->route('orders.index')->with(Flash::success('発注書を作成しました'));
    }

    /** 編集フォーム */
    public function edit(int $id, GetOrderUseCase $getOrder, ListCustomersUseCase $listCustomers): View
    {
        $order = $getOrder->execute($id);
        $customers = $listCustomers->execute();
        $customerNames = $this->customerNames($customers);

        return view('orders.form', [
            'customers' => CustomerView::collection($customers),
            'order' => OrderView::fromEntity($order, $customerNames[$order->customerId()] ?? ''),
            'taxOptions' => TaxRate::options(),
        ]);
    }

    public function update(SaveOrderRequest $request, int $id, UpdateOrderUseCase $updateOrder): RedirectResponse
    {
        $updateOrder->execute($id, $request->toInput());

        return redirect()->route('orders.index')->with(Flash::success('発注書を更新しました'));
    }

    /** 詳細 */
    public function show(int $id, GetOrderUseCase $getOrder, ListCustomersUseCase $listCustomers): View
    {
        $order = $getOrder->execute($id);
        $customerNames = $this->customerNames($listCustomers->execute());

        return view('orders.view', [
            'order' => OrderView::fromEntity($order, $customerNames[$order->customerId()] ?? ''),
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
     * 一覧のステータスピルからの JSON リクエスト。
     * フロント (status.ts) は 200 を見て画面を再読み込みする。
     */
    public function setStatus(ChangeOrderStatusRequest $request, ChangeOrderStatusUseCase $changeStatus): JsonResponse
    {
        $order = $changeStatus->execute($request->orderId(), $request->statusKind());

        return response()->json([
            'status' => 200,
            'is_issued' => $order->issueStatus()->value,
            'is_ordered' => $order->orderStatus()->value,
        ]);
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
