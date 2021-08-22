<?php

namespace App\Console\Commands;

use App\Models\TodoItem;
use App\Models\TodoNotification;
use App\Models\User;
use App\Notifications\TodoReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendTodoNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'todo:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send todo notifications';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        $notifications = TodoNotification::all()->where('reminder_datetime','<=', Carbon::now()->toDateTimeString())
            ->where('sent','=',false);

        foreach($notifications as $notification) {

            $todoItem = TodoItem::all()->where('id',$notification->todo_item_id)->first();
            $user = User::all()->where('id',$todoItem->user_id)->first();
            $user->notify(new TodoReminder($todoItem, $notification));
            $notification->sent = true;
            $notification->save();

        }
        return 0;
    }
}
