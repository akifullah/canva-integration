<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CanvaDesign extends Model
{
    use HasFactory;

    protected $fillable = [
        'canva_link',
        'download_link',
        'expiry_date',
    ];
} 