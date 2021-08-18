## Todo Application 
### About
This Todo Application is built with Laravel 8, it uses passport to handle authentication tokens.



### Setup API application

- Run ``sail up`` to start the docker containers
- Run ``sail php artisan migrate && sail php artisan db:seed`` to migrate and seed the application
- Run ` sail php artisan passport:install` to setup passport
- Run ` sail php artisian storage:link` to setup storage
- Run `sail php artisan schedule:work` to start the scheduler and to send emails
  - Visit `http://localhost:8025/` to view the emails sent
- Run `sail php artisan l5-swagger:generate` to generate the api docs
- Browse to ``http://localhost/api/documentation`` then authenticate using the Login route
  - The details are `user@example.com` and `password`
  - Copy your returned token to the Authorize button using the following format `Bearer {token}`
    for example `Bearer eyJ0eXAiOiJKV1QiLCJ..`
