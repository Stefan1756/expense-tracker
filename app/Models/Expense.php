<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id', 
        'amount', 
        'spent_at', 
        'title', 
        'note'
    ];

    protected $casts = [
        'spent_at' => 'date',
    ];

    public function user() 
    { 
        return $this->belongsTo(User::class); 
    }

    public function Category() 
    { 
        return $this->belongsTo(Category::class); 
    }
}