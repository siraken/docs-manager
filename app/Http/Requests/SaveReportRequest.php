<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Application\Report\Input\ReportInput;
use Illuminate\Foundation\Http\FormRequest;

final class SaveReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 移植元は $request->all() をそのまま fill() に渡していて検証が無かった。
     *
     * 担当者・取引先・案件は id で受ける。移植元のフォームは名前の文字列を
     * user_id / client という別名で送っており、保存されるか、あるいは
     * ID カラムに名前が入るかのどちらかだった。
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'end_time' => ['nullable', 'date_format:H:i,H:i:s'],
            // 始業・終業が揃っていればサーバー側で計算し直すため、
            // その場合この値は使われない
            'work_time' => ['nullable', 'numeric', 'min:0', 'max:24'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'user_id' => '担当者',
            'customer_id' => '取引先',
            'project_id' => '案件',
            'title' => '件名',
            'description' => '詳細',
            'date' => '勤務日',
            'start_time' => '始業時刻',
            'end_time' => '終業時刻',
            'work_time' => '勤務時間',
        ];
    }

    public function toInput(): ReportInput
    {
        return new ReportInput(
            userId: $this->intOrNull('user_id'),
            customerId: $this->intOrNull('customer_id'),
            projectId: $this->intOrNull('project_id'),
            title: (string) $this->input('title'),
            description: $this->stringOrNull('description'),
            date: $this->input('date'),
            startTime: $this->input('start_time'),
            endTime: $this->input('end_time'),
            workTimeHours: $this->input('work_time'),
        );
    }

    private function stringOrNull(string $key): ?string
    {
        $value = $this->input($key);

        return $value === null || $value === '' ? null : (string) $value;
    }

    private function intOrNull(string $key): ?int
    {
        $value = $this->input($key);

        return $value === null || $value === '' ? null : (int) $value;
    }
}
