## Olybet task - Make bet

### Usage

#### Introduction
The Laravel framework is used for the assignment along with PHP 7.3. For this task the MySQL database and
Apache server were used. 

A working example was created for this task. It can be accessed in this url: 

http://olybet-task.sauliusda.eu

Also docker containers were created. There are few easy steps to install the project in local environment 
using docker. The description how to do it can be found at the bottom of this document.

For reference the original task can be found here: 

http://olybet-task.sauliusda.eu/files/task-description.pdf

One API endpoint ```/api/bet``` was created and only POST method can be used on it.

The data is written to database in one transaction. So if it is not successful, the transaction will be rolled back.

Also a database access was created to demonstrate the structure of the database:

http://olybet-task.sauliusda.eu/pma
```
u: olybet
p: olybet
```

#### Example calls

Calls were tested using Postman. This successful call can be imported to Postman:

http://olybet-task.sauliusda.eu/files/olybet-task.postman_collection.json

Request with an error:

```json
{
	"player_id": "2",
	"stake_amount": "1001",
	"selections": [
		{"id": 1, "odds": 1.601},
		{"id": 2, "odds": 1.105},
		{"id": 3, "odds": 0.9}
	]
}
```

Response (http code 400):


```json
{
    "errors": [
        {
            "code": 11,
            "message": "Insufficient balance"
        }
    ],
    "selections": [
        {
            "id": 3,
            "errors": [
                {
                    "code": 6,
                    "message": "Minimum odds are 1"
                }
            ]
        }
    ]
}
```

Successful request:
```json
{
	"player_id": "2",
	"stake_amount": "100",
	"selections": [
		{"id": 1, "odds": 1.601},
		{"id": 2, "odds": 1.105},
		{"id": 3, "odds": 2}
	]
}
```

If a request is successful, a response will be with empty body and http code 200.

### Installation

You can run it in local environment via Docker.

##### 1. Run docker compose
```bash
docker-compose up
```
##### 2. Run composer install inside olybet_task folder
```bash
composer install
```
##### 3. Then point your browser to `localhost:8090`. The database is preloaded and you can make calls to local environment.
