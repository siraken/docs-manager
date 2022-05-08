@extends('layouts/default')
@section('page')

<div class="row">
  <div class="col-12">
    <h4 class="heading">出張申請</h4>
    <a class="btn btn-light border" href="{{ route('trips.create') }}"><i class="bi bi-plus-circle me-2"></i>出張申請をする</a>
    <a class="btn btn-light border" href="{{ route('trips.create') }}"><i class="bi bi-download me-2"></i>CSV取り込み</a>
  </div>
</div>

<div class="row">
  <div class="col-12">

    <table class="table">
      <thead>
        <tr>
          <th>申請日</th>
          <th>出張先</th>
          <th class="hide-on-small-only">目的</th>
          <th class="hide-on-small-only">出発日</th>
          <th>申請者</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
          <?php //$trips = []; ?>
        <?php foreach ($trips as $row): ?>
        <tr>
          <td class="align-middle"><?= date('Y/m/d', strtotime($row->apply_date)) ?></td>
          <td class="align-middle"><?= ($row->dir) ?></td>
          <td class="align-middle" class="hide-on-small-only"><?= mb_strimwidth($row->purpose, 0, 30, "...") ?></td>
          <td class="align-middle" class="hide-on-small-only"><?= date('Y/m/d', strtotime($row->date_from)) ?></td>
          <td class="align-middle"><?= ($row->apply_person) ?></td>
          <td class="align-middle">
            <div class="dropdown">
              <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-gear-fill"></i>
              </button>
              <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                <li><a class="dropdown-item" href="{{ route('trips.pdf', ['id' => $row['id']]) }}">PDF</a></li>
                {{-- <li><a class="dropdown-item" href="{{ route('trips.view', ['id' => $row['id']]) }}">View</a></li> --}}
                {{-- <li><a class="dropdown-item" href="{{ route('trips.edit', ['id' => $row['id']]) }}">Edit</a></li> --}}
                <li>
                  {{-- <?= $this->Form->postLink(__('Delete'),
                    ['action' => 'delete', $row->id],
                    ['class' => 'dropdown-item'],
                    ['confirm' => __('Are you sure you want to delete # {0}?', $row->id)]
                  ) ?></li> --}}
              </ul>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>

</div>

@endsection
