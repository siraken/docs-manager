<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Report
 * 
 * @property int $id
 * @property int|null $user_id
 * @property int|null $client_id
 * @property string|null $title
 * @property string|null $description
 * @property float|null $work_time
 * @property Carbon|null $date
 * @property Carbon|null $start_time
 * @property Carbon|null $end_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Report extends Model
{
	protected $table = 'reports';

	protected $casts = [
		'user_id' => 'int',
		'client_id' => 'int',
		'work_time' => 'float',
		'date' => 'datetime',
		'start_time' => 'datetime',
		'end_time' => 'datetime'
	];

	protected $fillable = [
		'user_id',
		'client_id',
		'title',
		'description',
		'work_time',
		'date',
		'start_time',
		'end_time'
	];
}
