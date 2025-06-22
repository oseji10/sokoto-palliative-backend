<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    use HasFactory;

    public $table = 'transactions';
    protected $fillable = [
        'transactionId',
        'productId',
        'soldBy',
        'paymentMethod',
        'cost',
        'quantitySold',
        'lga',
    ];
    protected $primaryKey = 'id';

    public function products()
    {
        return $this->belongsTo(Product::class, 'productId', 'productId');
    } 

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'stockId', 'stockId');
    } 
}
