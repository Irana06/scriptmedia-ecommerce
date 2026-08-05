<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecurringInvoiceGenerated extends Notification
{
    use Queueable;

    public function __construct(public readonly Invoice $invoice) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invoice baru '.$this->invoice->invoice_number)
            ->greeting('Halo '.$this->invoice->tenant->owner->name.',')
            ->line('Invoice periode baru untuk '.$this->invoice->tenant->name.' telah diterbitkan.')
            ->line('Total: Rp'.number_format((float) $this->invoice->total, 0, ',', '.'))
            ->line('Jatuh tempo: '.$this->invoice->due_date->format('d M Y'))
            ->action('Lihat invoice', route('portal.tenants.show', $this->invoice->tenant))
            ->line('Abaikan pesan ini bila pembayaran sudah diproses oleh tim ScriptMedia.');
    }

    /** @return array{recipient: string, message: string} */
    public function toWhatsAppPayload(): array
    {
        return [
            'recipient' => $this->invoice->tenant->owner->email,
            'message' => "Invoice {$this->invoice->invoice_number} sebesar Rp".number_format((float) $this->invoice->total, 0, ',', '.').' telah diterbitkan.',
        ];
    }
}
