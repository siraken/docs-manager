<script lang="ts">
  import { untrack } from "svelte";
  import { useForm } from "@inertiajs/svelte";

  import Button from "../../components/ui/Button.svelte";
  import Card from "../../components/ui/Card.svelte";
  import FormErrors from "../../components/ui/FormErrors.svelte";
  import Input from "../../components/ui/Input.svelte";
  import Label from "../../components/ui/Label.svelte";
  import PageHeader from "../../components/ui/PageHeader.svelte";
  import Select from "../../components/ui/Select.svelte";
  import Textarea from "../../components/ui/Textarea.svelte";
  import type { MasterOption } from "../../lib/master-types";
  import type { Report } from "../../lib/report-types";

  /**
   * 勤務報告の登録・編集フォーム。
   *
   * 移植前は担当者・取引先のセレクトが名前の文字列を user_id / client という
   * 名前で送っており、案件のセレクトにいたっては name 属性が空で送信すら
   * されていなかった。いずれも id で送る。
   */
  type Props = {
    report: Report | null;
    /** 新規のときだけ届く。日付や担当者の既定値はサーバーが決める */
    defaults: { date: string; userId: number | null } | null;
    users: MasterOption[];
    customers: MasterOption[];
    projects: MasterOption[];
    urls: { submit: string; back: string };
  };

  let { report, defaults, users, customers, projects, urls }: Props = $props();

  const isNew = $derived(report === null);

  const form = untrack(() =>
    useForm({
      date: report?.date ?? defaults?.date ?? "",
      title: report?.title ?? "",
      user_id: report?.userId ?? defaults?.userId ?? "",
      customer_id: report?.customerId ?? "",
      project_id: report?.projectId ?? "",
      start_time: report?.startTime ?? "",
      end_time: report?.endTime ?? "",
      work_time: report ? String(report.workHours) : "",
      description: report?.description ?? "",
    }),
  );

  /**
   * 始業・終業が両方揃っていれば勤務時間はサーバーが計算し直す。
   * 手入力欄はそのとき読み取り専用にして、入力しても無視されることを示す。
   */
  const computed = $derived(form.start_time !== "" && form.end_time !== "");

  /** 画面に出す見込み時間。正はサーバー側の Report エンティティ */
  const preview = $derived.by(() => {
    if (!computed) {
      return null;
    }

    const [startHour, startMinute] = String(form.start_time).split(":").map(Number);
    const [endHour, endMinute] = String(form.end_time).split(":").map(Number);

    const diff = endHour * 60 + endMinute - (startHour * 60 + startMinute);
    // 日跨ぎ (22:00 -> 02:00) は翌日として扱う。TimeOfDay と同じ規則
    const minutes = diff > 0 ? diff : diff + 24 * 60;

    return `${Math.floor(minutes / 60)}:${String(minutes % 60).padStart(2, "0")}`;
  });

  function save(): void {
    form.post(urls.submit);
  }
</script>

<PageHeader title={isNew ? "勤務報告の登録" : "勤務報告の編集"}>
  {#snippet actions()}
    <Button href={urls.back} icon="arrow-left">戻る</Button>
    <Button variant="primary" icon="check-lg" disabled={form.processing} onclick={save}>保存する</Button>
  {/snippet}
</PageHeader>

<div class="max-w-3xl">
  <FormErrors errors={form.errors} />

  <Card class="space-y-5">
    <div class="grid gap-4 sm:grid-cols-3">
      <div>
        <Label for="date" required>勤務日</Label>
        <Input type="date" id="date" bind:value={form.date} required />
      </div>
      <div class="sm:col-span-2">
        <Label for="title" required>件名</Label>
        <Input id="title" bind:value={form.title} required />
      </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
      <div>
        <Label for="start_time">始業時刻</Label>
        <Input type="time" id="start_time" bind:value={form.start_time} />
      </div>
      <div>
        <Label for="end_time">終業時刻</Label>
        <Input type="time" id="end_time" bind:value={form.end_time} />
      </div>
      <div>
        <Label for="work_time">勤務時間 (時間)</Label>
        <Input
          type="number"
          id="work_time"
          step="0.25"
          min="0"
          max="24"
          bind:value={form.work_time}
          readonly={computed}
        />
        <p class="mt-1.5 text-xs text-slate-500">
          {#if computed}
            始業・終業から {preview} として計算されます
          {:else}
            始業・終業を両方入れると自動で計算されます
          {/if}
        </p>
      </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
      <div>
        <Label for="user_id">担当者</Label>
        <Select id="user_id" bind:value={form.user_id}>
          <option value="">選択してください</option>
          {#each users as user (user.id)}
            <option value={user.id}>{user.name}</option>
          {/each}
        </Select>
      </div>

      <div>
        <Label for="customer_id">取引先</Label>
        <Select id="customer_id" bind:value={form.customer_id}>
          <option value="">選択してください</option>
          {#each customers as customer (customer.id)}
            <option value={customer.id}>{customer.name}</option>
          {/each}
        </Select>
      </div>

      <div>
        <Label for="project_id">案件</Label>
        <Select id="project_id" bind:value={form.project_id}>
          <option value="">選択してください</option>
          {#each projects as project (project.id)}
            <option value={project.id}>{project.name}</option>
          {/each}
        </Select>
      </div>
    </div>

    <div>
      <Label for="description">詳細</Label>
      <Textarea id="description" rows={5} bind:value={form.description} />
    </div>
  </Card>
</div>
