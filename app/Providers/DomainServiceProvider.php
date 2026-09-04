<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Auth\Port\AuthSessionInterface;
use App\Application\Auth\Port\LoginNotifierInterface;
use App\Application\Freee\Port\FreeeApiClientInterface;
use App\Application\Order\Port\OrderCsvExporterInterface;
use App\Application\Order\Port\OrderPdfRendererInterface;
use App\Application\SpreadSheet\Port\SpreadSheetWriterInterface;
use App\Application\Travel\Port\CsvReaderInterface;
use App\Application\Travel\Port\TravelExpensePdfRendererInterface;
use App\Application\Travel\Port\TravelPdfRendererInterface;
use App\Application\User\Port\TwoFactorSetupStoreInterface;
use App\Domain\Academy\Repository\AcademyInquiryRepositoryInterface;
use App\Domain\Accounting\Repository\AccountRepositoryInterface;
use App\Domain\Accounting\Repository\JournalEntryRepositoryInterface;
use App\Domain\Contract\Repository\ContractRepositoryInterface;
use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use App\Domain\Learning\Repository\AssignmentRepositoryInterface;
use App\Domain\Learning\Repository\CourseRepositoryInterface;
use App\Domain\Learning\Repository\EnrollmentRepositoryInterface;
use App\Domain\Learning\Repository\SubmissionRepositoryInterface;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\Report\Repository\ReportRepositoryInterface;
use App\Domain\Setting\Repository\CompanyProfileRepositoryInterface;
use App\Domain\Travel\Repository\TravelExpenseRepositoryInterface;
use App\Domain\Travel\Repository\TravelRepositoryInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Service\PasswordHasherInterface;
use App\Infrastructure\Auth\LaravelPasswordHasher;
use App\Infrastructure\Auth\SessionAuthStore;
use App\Infrastructure\Auth\SessionTwoFactorSetupStore;
use App\Infrastructure\Csv\OrderCsvExporter;
use App\Infrastructure\Csv\SplFileObjectCsvReader;
use App\Infrastructure\Freee\CurlFreeeApiClient;
use App\Infrastructure\Mail\MailLoginNotifier;
use App\Infrastructure\Pdf\TcpdfOrderPdfRenderer;
use App\Infrastructure\Pdf\TcpdfTravelExpensePdfRenderer;
use App\Infrastructure\Pdf\TcpdfTravelPdfRenderer;
use App\Infrastructure\Persistence\Eloquent\AcademyInquiryRepository;
use App\Infrastructure\Persistence\Eloquent\AssignmentRepository;
use App\Infrastructure\Persistence\Eloquent\AccountRepository;
use App\Infrastructure\Persistence\Eloquent\CompanyProfileRepository;
use App\Infrastructure\Persistence\Eloquent\CourseRepository;
use App\Infrastructure\Persistence\Eloquent\ContractRepository;
use App\Infrastructure\Persistence\Eloquent\CustomerRepository;
use App\Infrastructure\Persistence\Eloquent\EnrollmentRepository;
use App\Infrastructure\Persistence\Eloquent\JournalEntryRepository;
use App\Infrastructure\Persistence\Eloquent\OrderRepository;
use App\Infrastructure\Persistence\Eloquent\ProjectRepository;
use App\Infrastructure\Persistence\Eloquent\SubmissionRepository;
use App\Infrastructure\Persistence\Eloquent\ReportRepository;
use App\Infrastructure\Persistence\Eloquent\TravelExpenseRepository;
use App\Infrastructure\Persistence\Eloquent\TravelRepository;
use App\Infrastructure\Persistence\Eloquent\UserRepository;
use App\Infrastructure\SpreadSheet\GoogleSheetsClient;
use Illuminate\Support\ServiceProvider;

/**
 * ドメイン層・アプリケーション層が宣言した抽象と、Infrastructure 層の実装を結ぶ。
 *
 * 依存の向きはここで一箇所に閉じている。ユースケースは実装クラスを知らず、
 * コンストラクタでインターフェースを受け取るだけなので、テストでは
 * $this->app->instance(...) で差し替えられる。
 */
final class DomainServiceProvider extends ServiceProvider
{
    /**
     * インターフェース => 実装。
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        // --- リポジトリ ---
        OrderRepositoryInterface::class => OrderRepository::class,
        CustomerRepositoryInterface::class => CustomerRepository::class,
        ProjectRepositoryInterface::class => ProjectRepository::class,
        ContractRepositoryInterface::class => ContractRepository::class,
        ReportRepositoryInterface::class => ReportRepository::class,
        AccountRepositoryInterface::class => AccountRepository::class,
        JournalEntryRepositoryInterface::class => JournalEntryRepository::class,
        CourseRepositoryInterface::class => CourseRepository::class,
        EnrollmentRepositoryInterface::class => EnrollmentRepository::class,
        AssignmentRepositoryInterface::class => AssignmentRepository::class,
        SubmissionRepositoryInterface::class => SubmissionRepository::class,
        UserRepositoryInterface::class => UserRepository::class,
        TravelRepositoryInterface::class => TravelRepository::class,
        TravelExpenseRepositoryInterface::class => TravelExpenseRepository::class,
        AcademyInquiryRepositoryInterface::class => AcademyInquiryRepository::class,
        CompanyProfileRepositoryInterface::class => CompanyProfileRepository::class,

        // --- ドメインサービス ---
        PasswordHasherInterface::class => LaravelPasswordHasher::class,

        // --- ポート (外部との境界) ---
        AuthSessionInterface::class => SessionAuthStore::class,
        LoginNotifierInterface::class => MailLoginNotifier::class,
        TwoFactorSetupStoreInterface::class => SessionTwoFactorSetupStore::class,
        OrderPdfRendererInterface::class => TcpdfOrderPdfRenderer::class,
        OrderCsvExporterInterface::class => OrderCsvExporter::class,
        TravelPdfRendererInterface::class => TcpdfTravelPdfRenderer::class,
        TravelExpensePdfRendererInterface::class => TcpdfTravelExpensePdfRenderer::class,
        CsvReaderInterface::class => SplFileObjectCsvReader::class,
        FreeeApiClientInterface::class => CurlFreeeApiClient::class,
        SpreadSheetWriterInterface::class => GoogleSheetsClient::class,
    ];
}
