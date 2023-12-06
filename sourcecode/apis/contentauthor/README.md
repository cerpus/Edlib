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

## Configure Article Storage

The default is to use the `public/h5pstorage` directory. It should not be neccessary to configure this unless you have a more production / scaled-up setup like using Amazon S3 for storage for instance.

In `.env`

```
UPLOAD_STORAGE_DRIVER=<local>
```

UPLOAD_STORAGE_DRIVER: the driver to be used, defaults to local

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
