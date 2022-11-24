<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed|string $file_name
 * @property mixed|string $file_content
 */
class TorrentHistory extends Model
{
    use HasFactory;
}
