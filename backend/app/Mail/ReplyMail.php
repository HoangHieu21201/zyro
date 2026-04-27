<?php

namespace App\Mail;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// ĐÃ FIX: Xóa bỏ 'implements ShouldQueue' để ép Laravel gửi email trực tiếp
// Việc này giúp hệ thống bắt được chính xác lỗi SMTP và ném ra màn hình thay vì chết ngầm.
class ReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contact;
    public $replyMessage;

    /**
     * Khởi tạo Message
     */
    public function __construct(Contact $contact, $replyMessage)
    {
        $this->contact = $contact;
        $this->replyMessage = $replyMessage;
    }

    /**
     * Tiêu đề Email
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Phản hồi từ ZYRO: ' . $this->contact->subject,
        );
    }

    /**
     * View hiển thị Email
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact_reply',
        );
    }
}