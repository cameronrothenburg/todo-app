<br/>
<p align="center">
  <h3 align="center">Todo Application</h3>

  <p align="center">
    A todo application built with Laravel 8!
    <br/>
    <br/>
    <a href="https://github.com/rotho98/todo-app/issues">Report Bug</a>
  </p>


![Contributors](https://img.shields.io/github/contributors/rotho98/todo-app?color=dark-green) ![Forks](https://img.shields.io/github/forks/rotho98/todo-app?style=social) ![Stargazers](https://img.shields.io/github/stars/rotho98/todo-app?style=social) ![Issues](https://img.shields.io/github/issues/rotho98/todo-app) ![License](https://img.shields.io/github/license/rotho98/todo-app)

## Table Of Contents

* [About the Project](#about-the-project)
* [Built With](#built-with)
* [Getting Started](#getting-started)
    * [Prerequisites](#prerequisites)
    * [Installation](#installation)
* [License](#license)
* [Authors](#authors)

## About The Project

A simple todo application with security and scalability in mind.
Models have UUIDs as the primary key to prevent attacks with enumeration.

Uploaded files are stored in UUID folders and can be configured to only allow specific MIME Types. Once again this makes enumerating through public files a lot harder.
Files can be saved and retrieved from any storage location, multiple storage locations are supported at any one time.
Max upload size is left for php.ini to decide.

The API is secured with a Bearer token to only allow registered users to interact with the application.
Laravel passport to generate the token and validate it.

Searched results are stored in Cache to allow for faster retrieval for each subsequent request.
Cache is reset when an todoItem is saved/updated. This is to ensure cache is updated.
I have used Redis for the cache driver.


Notifications can be added to todo items, the user will receive an email when the notification is due.
Updating the todo items due date to before a notification due date will automatically delete the notification.
Emails are trigger through an artisan command that runs every minute.


## Built With



* [Laravel 8 ](https://laravel.com/)
* [Laravel Passport](https://laravel.com/docs/8.x/passport)
* [L5-Swagger](https://github.com/DarkaOnLine/L5-Swagger)
* [Redis](https://redis.io)

## Getting Started

To get a local copy up and running follow these simple steps.

### Prerequisites

* [Docker](https://www.docker.com/)
* Linux Distribution Or WSL

### Installation


- Rename `.env.example` to `.env`
- Run the setup commands [here](https://laravel.com/docs/8.x/sail#installing-composer-dependencies-for-existing-projects) for laravel sail  
- In the root of the project run `` alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail' ``
- Run ``sail up`` to start the docker containers
  - Append `-d` to run in the background
  - Run ``sail php artisan key:generate`` to generate the random application key
  - Run ``sail php artisan migrate && sail php artisan db:seed`` to migrate and seed the application
  - Run ` sail php artisan passport:install` to setup passport
  - Run ` sail php artisan storage:link` to setup storage
  - Run `sail php artisan l5-swagger:generate` to generate the api docs
  - Run `sail php artisan schedule:work` to start the scheduler and to send emails
      - Visit `http://localhost:8025/` to view the emails sent
  - Browse to ``http://localhost/api/documentation`` then authenticate using the Login route
      - The details are `user@example.com` and `password`
      - Copy your returned token to the Authorize button using the following format `Bearer {token}`
        for example `Bearer eyJ0eXAiOiJKV1QiLCJ..`

If you don't want to run this command by command do the first two steps, then run ``alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail' && sail up -d && sail php artisan key:generate && sail php artisan migrate && sail php artisan db:seed && sail php artisan passport:install && sail php artisan storage:link && sail php artisan l5-swagger:generate && sail php artisan schedule:work ``
This will run the commands one after another. Bear in mind the last command will stay open as its the scheduler.

## Notes
If for any reason installation fails with errors about not being able to connect to mysql run this
- `sail down -v`
- `docker system prune` to remove containers 
- `sail build --no-cache` to build with new images
- Redo setup instructions from step 3 step by step! Don't use the chained command!
    - Run ``sail php artisan migrate:fresh`` instead of ``sail php artisan migrate``
## License

Distributed under the MIT License. See [LICENSE](https://github.com/rotho98/todo-app/blob/main/LICENSE.md) for more information.

## Authors

* **Cameron-Lee Rothenburg** - *Software Developer* - [Cameron-Lee Rothenburg](https://github.com/rotho98) - *Built the project*
