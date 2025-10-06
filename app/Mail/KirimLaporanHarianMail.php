<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class KirimLaporanHarianMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $tanggal;
    public $outlet;

    public function __construct($data, $tanggal, $outlet)
    {
        $this->data = $data;
        $this->tanggal = $tanggal;
        $this->outlet = $outlet;
    }

    public function build()
    {
        return $this->subject('Laporan Harian ' . $this->outlet->nama_outlet)
            ->view('emails.laporan_harian');
    }
}
