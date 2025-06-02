<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserNotification extends Notification
{
    use Queueable;

    public $title;
    public $body;
    public $type;
    public $goal_id;
    public $goal_status;
	public function __construct($title = null, $body = null,$type=null, $goal_id =null,$goal_status =null) {


		$this->title = $title;
		$this->body = $body;
        $this->type = $type;
        $this->goal_id = $goal_id;
        $this->goal_status = $goal_status;
	}

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
     public function via($notifiable) {
		return ['database'];
	}

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */

    public function toDatabase($notifiable) {
		return [
			'title' => $this->title,
			'body' => $this->body,
			'type' => $this->type,
            'goal_id' => $this->goal_id,
            'goal_status' => $this->goal_status
		];
	}



    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
