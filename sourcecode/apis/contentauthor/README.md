# Installing

Install NodeJs, gulp and composer.

```
¦> npm install
¦> composer install
```

Now, you need to create a symlink from the h5p-php-library to your public dir

You need to expose the core H5P libraries from the composer package:

```
¦> ln -s vendor/h5p/h5p-core public/h5p-php-library
|> ln -s vendor/h5p/h5p-editor public/h5p-editor-php-library
```

To expose the permanent file storage

```
¦> mkdir h5pstorage
¦> ln -s h5pstorage <laravel-install-dir>/public/h5pstorage
¦> gulp
```

# Configuration

Take a look in the `.env.example` file. It should contain all possible configuration keys. Not all are neccessary. We try to default to 'sane' defaults.

For develop environments the cloud storage can be switched off. To disable it put the following configuration in the `.env` file:

```
ENABLE_CLOUD_STORAGE=false
```

## Recommendation Engine

Currently reporting to the Recommendation Engine is disabled by default.
You may enable reporting to the Recommendation Engine like so in the `.env` file:

```
FEATURE_ENABLE_RECOMMENDATION_ENGINE=true
```

and configure the Recommendation Engine address like so:

```
RE_CONTENT_INDEX_URL=https://re-content-index
```

The default is given and should work out of the box in a production environment.

## Configure Article Storage

The default is to use the `public/h5pstorage` directory. It should not be neccessary to configure this unless you have a more production / scaled-up setup like using Amazon S3 for storage for instance.

In `.env`

```
UPLOAD_STORAGE_DRIVER=<local>
UPLOAD_STORAGE_PATH_ARTICLE=<.../public/h5pstorage/article-uploads>
UPLOAD_PUBLIC_PATH_ARTICLE=</h5pstorage/article-uploads>
```

UPLOAD_STORAGE_DRIVER: the driver to be used, defaults to local

UPLOAD_STORAGE_PATH_ARTICLE: upload path, defaults to public/h5pstorage/article-uploads

UPLOAD_PUBLIC_PATH_ARTICLE: public path to root of uploaded article files, defaults to /h5pstorage/article-uploads

# Running scheduled tasks

Content author will take care of running scheduled tasks when required provided the `php artisan schedule:run` task is run periodically.

The Docker image contains a cron job that fires this task every minute. See `docker/laravel.schedule`.

# Locking content for edit

To enable the content edit locking feature add

```
FEATURE_CONTENT_LOCKING=true
```

to the `.env` file

Make sure the artisan command `schedule:run` is running once per minute and the removal of stale locks will happen automatically.

You can run the command manually

```
php artisan cerpus:remove-content-locks
```

# Initialize versioning

Create the initial version of all content run the following command

```
$ CACHE_DRIVER=file php artisan cerpus:init-versioning
```

# Starting the internal queue worker

#### `This refers to the internal queues, not the RabbitMQ queue workers`

[Laravel Horizon](https://laravel.com/docs/6.x/horizon) will handle all the internal queues.

See `config/horizon.php` for config options if you want to modify/tweak the set up.

Start horizon like this:

```bash
$ php artisan horizon
```

Be sure to set `QUEUE_DRIVER=redis` in `.env`.

[In production use Supervisor to start and monitor Horizon](https://laravel.com/docs/6.x/horizon#deploying-horizon).

For development purposes you can use the `sync` queue driver.

## Enabling context collaboration message processing

In .env
`FEATURE_CONTEXT_COLLABORATION=true`

## Creating a new message handler

To create a new message processing worker follow these steps. In the example we will consume a queue named `my_messages`

1. `php artisan bowler:make:queue my_messages MyMessages`
2. The my_messages queue has been added to `app/Messaging/queue.php`. Edit this file to finish setting up the queue. Refer to the Bowler docs / source code for config options.
3. To implement the handler edit `app/Messaging/Handlers/MyMessageHandler.php@handle`

```
public function handle($msg)
{
    echo "Look, I'm processing the {$msg->body}\n";
}
```

I recommend farming out the actual processing of the message to an entity outside the message handler to ease the testability and portability of the processing should we decide to replace RabbitMQ in the future. See `app/Messaging/Handlers/EdStepCollaborationhandler@handle`

## Starting the worker from the command line

1. `php artisan bowler:consume my_messages`

If you publish a message to the exchange configured in `queue.php` you should see the message being received and processed by Content Author.

## Handling the worker in production

Use Supervisor or something similar to start the workers. You will need one supervisor config per queue, but may start multiple workers to handle the queue on capacity problems (see `numprocs_start`). In a setup where content author is scaled horizontally only one server needs to run the worker. The workers may run on a dedicated machine.

### Configuring the RabbitMQ connection

These .env variables (with defaults) are available for configuration.

```
RABBITMQ_HOST=localhost
RABBITMQ_PORT=5672
RABBITMQ_USERNAME=guest
RABBITMQ_PASSWORD=guest
```

### Starting the edstepmessages worker

Assumes that Content Author is installed in /var/www/content-author.
This will start two new workers handling events on the edstep_messages queue on system boot.
Save this as /etc/supervisor/config/edstepmessages.conf

```
[program:edstepmessages]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /var/www/content-author/artisan bowler:consume ca-EdStep-CollaborationUpdates
directory=/var/www/content-author/
user=www-data
group=www-data
numprocs=2
autostart=true
```

```
$ sudo supervisorctl reread
$ sudo supervisorctl start edstepmessages
```

See [https://laravel.com/docs/6.x/queues#supervisor-configuration](https://laravel.com/docs/6.x/queues#supervisor-configuration) for another example directly related to Laravel queues

### Restart queue worker on update

As the queue handlers are long lived processes you must restart them when the code has been updated. Add `supervisorctl restart edstepmessages` after migrations are run.

# LTI params

Content Author will use some LTI parameters if passed in.

## `ext_question_set`

If `ext_question_set` is set on a call to `POST` `/questionset/create` Content Author will pre fill the QuestionSet editor with the data. The data must be a base64 encoded json

Example:

```json
{
  "title": "My Question Set ",
  "tags": ["tag1", "tag2"],
  "questions": [
    {
      "text": "How are you?",
      "answers": [
        {
          "text": "Fine",
          "correct": true
        },
        {
          "text": "So, so...",
          "correct": false
        },
        {
          "text": "Horrible!",
          "correct": false
        }
      ]
    },
    {
      "text": "Where are you?",
      "answers": [
        {
          "text": "At work",
          "correct": true
        },
        {
          "text": "Home",
          "correct": false
        },
        {
          "text": "On the bus",
          "correct": false
        }
      ]
    }
  ]
}
```

The tags property is optional.

## `ext_create_content_default_license`

If `ext_create_content_default_license` is set you can set the default license when you create new content.

Value is one of: `PRIVATE`, `CC0`, `BY`, `BY-SA`, `BY-NC`, `BY-ND`, `BY-NC-SA` or `BY-NC-ND`

## How to add params to your LTI request

Example

```php
    $lti = new BasicLTI();
...
    $lti->setExtraLti([
        'ext_question_set' => base64_encode(json_encode($questionSet)),
        'ext_create_content_default_license' => 'BY-SA'
    ]);
...
    $form = $lti->getForm();
```
