<?php

namespace App\Notifications;

use App\Models\TodoItem;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TodoReminder extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param $todoItemId ID of the todoItem
     */
    public function __construct($todoItemId)
    {
        $this->todoId = $todoItemId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $todoItem = TodoItem::all()->where('id', $this->todoId)->first();
        $dateDiff = Carbon::parse($todoItem->due_datetime)->diffForHumans(Carbon::now());

        return (new MailMessage)
                    ->subject('Your Todo Reminder!')
                    ->greeting('Hello!')
                    ->line("Your todo titled '{$todoItem->title}' is due in ${$dateDiff}")
                    ->line('Thank you!');
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
