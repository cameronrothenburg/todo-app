<?php

namespace App\Notifications;

use App\Models\TodoItem;
use App\Models\TodoNotification;
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
     * @var TodoNotification
     */
    private $todoNotification;
    /**
     * @var TodoItem
     */
    private $todoItem;

    /**
     * Create a new notification instance.
     *
     * @param $todoItem TodoItem The todoItem
     */
    public function __construct(TodoItem $todoItem, TodoNotification $todoNotification)
    {
        $this->todoItem = $todoItem;
        $this->todoNotification = $todoNotification;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable): array {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage {

        $dateDiff = Carbon::parse($this->todoItem->due_datetime)->longRelativeDiffForHumans
        ($this->todoNotification->reminder_datetime, CarbonInterface::DIFF_ABSOLUTE);

        return (new MailMessage)
                    ->subject('Your Todo Reminder!')
                    ->greeting('Hello!')
                    ->line("Your todo titled '{$this->todoItem->title}' is due in {$dateDiff}!")
                    ->line('Thank you!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable): array {
        return [
            //
        ];
    }
}
