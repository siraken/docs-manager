<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Contract
 * 
 * @property int $id
 * @property int|null $client_id
 * @property string|null $contract_id
 * @property string|null $name
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Contract extends Model
{
	protected $table = 'contracts';

	protected $casts = [
		'client_id' => 'int',
		'start_date' => 'datetime',
		'end_date' => 'datetime'
	];

	protected $fillable = [
		'client_id',
		'contract_id',
		'name',
		'start_date',
		'end_date',
		'description'
	];
}
