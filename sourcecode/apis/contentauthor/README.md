# Configuration

Take a look in the `.env.example` file. It should contain all possible configuration keys. Not all are neccessary. We try to default to 'sane' defaults.

# Initialize versioning

Create the initial version of all content run the following command

```
$ CACHE_DRIVER=file php artisan cerpus:init-versioning
```

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
