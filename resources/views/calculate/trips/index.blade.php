@extends('layouts/default')
@section('page')

<div class="row">
  <div class="col-12">
    <h4 class="heading">出張申請</h4>
    <a class="btn btn-light border" href="/trips/create"><i class="bi bi-plus-circle me-2"></i>出張申請をする</a>
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
        <?php foreach ($trips as $trip): ?>
        <tr>
          <td class="align-middle"><?= date('Y/m/d', strtotime($trip->apply_date)) ?></td>
          <td class="align-middle"><?= ($trip->dir) ?></td>
          <td class="align-middle" class="hide-on-small-only"><?= mb_strimwidth($trip->purpose, 0, 30, "...") ?></td>
          <td class="align-middle" class="hide-on-small-only"><?= date('Y/m/d', strtotime($trip->date_from)) ?></td>
          <td class="align-middle"><?= ($trip->apply_person) ?></td>
          <td class="align-middle">
            <div class="dropdown">
              <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-gear-fill"></i>
              </button>
              <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                <li><a class="dropdown-item" href="/trips/pdf/<?=$trip->id?>">PDF</a></li>
                <li><a class="dropdown-item" href="/trips/view/<?=$trip->id?>">View</a></li>
                <li><a class="dropdown-item" href="/trips/edit/<?=$trip->id?>">Edit</a></li>
                <li>
                  {{-- <?= $this->Form->postLink(__('Delete'),
                    ['action' => 'delete', $trip->id],
                    ['class' => 'dropdown-item'],
                    ['confirm' => __('Are you sure you want to delete # {0}?', $trip->id)]
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
