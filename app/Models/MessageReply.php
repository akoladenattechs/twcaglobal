<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $message_id
 * @property string $reply_subject
 * @property string $reply_message
 * @property int $sent_by
 * @property string|null $sent_at
 * @property-read ContactMessage|null $contactMessage
 * @property-read User|null $sentBy
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageReply newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageReply newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageReply query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageReply whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageReply whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageReply whereReplyMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageReply whereReplySubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageReply whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageReply whereSentBy($value)
 *
 * @mixin Model
 */
class MessageReply extends Model
{
    protected $table = 'message_replies';

    public $timestamps = false;

    protected $fillable = ['message_id', 'reply_subject', 'reply_message', 'sent_by', 'sent_at'];

    public function contactMessage()
    {
        return $this->belongsTo(ContactMessage::class, 'message_id');
    }

    public function sentBy()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
