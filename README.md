<br/>
<p align="center">
  <h3 align="center">Todo Application</h3>

  <p align="center">
    A todo application built with Laravel 8!
    <br/>
    <br/>
    <a href="https://github.com/rotho98/todo-app/issues">Report Bug</a>
  </p>
</p>

![Downloads](https://img.shields.io/github/downloads/rotho98/todo-app/total) ![Contributors](https://img.shields.io/github/contributors/rotho98/todo-app?color=dark-green) ![Forks](https://img.shields.io/github/forks/rotho98/todo-app?style=social) ![Stargazers](https://img.shields.io/github/stars/rotho98/todo-app?style=social) ![Issues](https://img.shields.io/github/issues/rotho98/todo-app) ![License](https://img.shields.io/github/license/rotho98/todo-app)

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

The API is secured with a Bearer token to only allow registered users to interact with the application.


## Built With



* [Laravel 8 ](https://laravel.com/)
* [Laravel Passport](https://laravel.com/docs/8.x/passport)
* [L5-Swagger](https://github.com/DarkaOnLine/L5-Swagger)

## Getting Started

To get a local copy up and running follow these simple steps.

### Prerequisites

This project uses [Laravel Sail](https://laravel.com/docs/8.x/sail), follow the instructions to set up Sail's prerequisites for your machine.

### Installation

These instructions assume you already have the repo cloned.

- In the root of the project run `` alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail' ``
- Run ``sail up`` to start the docker containers
- Run ``sail php artisan migrate && sail php artisan db:seed`` to migrate and seed the application
- Run ` sail php artisan passport:install` to setup passport
- Run ` sail php artisian storage:link` to setup storage
- Run `sail php artisan l5-swagger:generate` to generate the api docs
- Run `sail php artisan schedule:work` to start the scheduler and to send emails
    - Visit `http://localhost:8025/` to view the emails sent
- Browse to ``http://localhost/api/documentation`` then authenticate using the Login route
    - The details are `user@example.com` and `password`
    - Copy your returned token to the Authorize button using the following format `Bearer {token}`
      for example `Bearer eyJ0eXAiOiJKV1QiLCJ..`

If you don't want to run this command by command run the first two commands, then run `` sail php artisan migrate && sail php artisan db:seed && sail php artisan passport:install && sail php artisian storage:link && sail php artisan l5-swagger:generate && sail php artisan schedule:work ``
This will run the commands one after another. Bear in mind the last command will stay open as its the scheduler.


### Creating A Pull Request



## License

Distributed under the MIT License. See [LICENSE](https://github.com/rotho98/todo-app/blob/main/LICENSE.md) for more information.

## Authors

* **Cameron-Lee Rothenburg** - *Software Developer* - [Cameron-Lee Rothenburg](https://github.com/rotho98) - *Built the project*
