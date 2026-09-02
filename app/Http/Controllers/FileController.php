<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * ファイルの受け渡し。
 *
 * 移行前は routes/web.php にクロージャとして書かれており、次の問題があった。
 *  - アップロードで $_POST / $_FILES を直接読み、ファイル名を検証せずに
 *    storage のパスへ連結していた (パストラバーサル)
 *  - ダウンロードと削除もルートパラメータをそのままパスに連結していた
 *  - アップロード時のバリデーションが一切無かった
 *
 * ファイル名はすべて basename() で潰したうえで、実パスが保存先ディレクトリの
 * 内側にあることを確認してから触る。
 *
 * TODO: アップロードは認証なしで実行できる (取引先にファイルを送ってもらう
 *       想定と思われる)。意図した仕様かは確認が要る。少なくともサイズ制限と
 *       レート制限は入れておきたい。
 */
final class FileController extends Controller
{
    private const UPLOAD_DIR = 'app/public/uploads';

    public function index(): View
    {
        return view('files.index', [
            // 一覧はログイン中のみ出す (ビュー側も session('email') を見ている)
            'files' => session('email') === null ? [] : $this->listFiles(),
        ]);
    }

    public function download(string $file): BinaryFileResponse
    {
        return response()->download($this->resolve($file));
    }

    public function upload(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'file' => ['required', 'file', 'max:51200'],
        ]);

        $upload = $request->file('file');

        // 送信者名をファイル名の先頭に付ける運用は残す。ただしパス区切りや
        // 制御文字が混ざらないよう、双方をファイル名として無害な形に潰す。
        $prefix = $this->sanitiseSegment((string) ($validated['name'] ?? ''));
        $original = $this->sanitiseSegment(basename($upload->getClientOriginalName()));
        $fileName = $prefix === '' ? $original : $prefix . '_' . $original;

        $upload->move($this->uploadDir(), $fileName);

        return redirect()->route('files.index')->with(Flash::success('アップロードしました'));
    }

    public function delete(string $file): RedirectResponse
    {
        unlink($this->resolve($file));

        return redirect()->route('files.index')->with(Flash::success('削除しました'));
    }

    /** @return list<string> */
    private function listFiles(): array
    {
        $dir = $this->uploadDir();

        if (!is_dir($dir)) {
            return [];
        }

        return array_values(array_diff(scandir($dir) ?: [], ['.', '..']));
    }

    private function uploadDir(): string
    {
        return storage_path(self::UPLOAD_DIR);
    }

    /**
     * ルートパラメータのファイル名を実パスに直す。
     * 保存先ディレクトリの外を指していたら 404 にする。
     */
    private function resolve(string $file): string
    {
        $path = realpath($this->uploadDir() . '/' . basename($file));
        $base = realpath($this->uploadDir());

        if ($path === false || $base === false || !str_starts_with($path, $base . DIRECTORY_SEPARATOR)) {
            abort(404);
        }

        return $path;
    }

    /** ファイル名の 1 区切り分として安全な文字だけ残す */
    private function sanitiseSegment(string $value): string
    {
        $value = str_replace(["\0", '/', '\\'], '', $value);
        $value = preg_replace('/[\x00-\x1f]/', '', $value) ?? '';

        return trim($value, ". \t\n\r");
    }
}
