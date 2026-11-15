<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    public const SUBJECTS = [
        'general' => 'استفسار عام',
        'wholesale' => 'طلب بالجملة',
        'partnership' => 'شراكة وتوزيع',
        'other' => 'أخرى',
    ];

    protected $fillable = ['name', 'phone', 'email', 'subject', 'message', 'ip', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function subjectLabel(): string
    {
        return self::SUBJECTS[$this->subject] ?? (string) $this->subject;
    }
}
