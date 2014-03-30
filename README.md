### The Exercise

Create a REST Controller to handle the following HTTP requests based on the information included in [cities.csv]() and [users.csv]().  Provide a code sample as well as the database structure you use to implement your solution.  Please consider how to deal with bad requests, how to respond to requests with large datasets, and what additional structures may be needed to track user visits.


- List all cities in a state

```
GET /v1/states/<STATE>/cities.json
```

- List cities within a 100 mile radius of a city
```
GET /v1/states/<STATE>/cities/<CITY>.json?radius=100
```

- Allow a user to update a row of data to indicate they have visited a particular city.
```
POST /v1/users/<USER_ID>/visits

{
	'city' : <CITY>,
	'state' : <STATE>
}
```

- Return a list of cities the user has visited

```
GET /v1/users/<USER_ID>/visits
```


### MSM's Solution

I've built a solution to this exercise that runs on PHP 5.3 or better. I expect that you have the follwing software installed:

- Vagrant, for provisioning the virtual machine this runs on
    - Optionally, the vagrant-hosts plugin, for setting your hosts file automatically.
- Virtualbox
- Composer, if you do any PHP package installation from localhost rather than on the VM.
    - In case composer isn't installed globally on the VM by Puppet (which happens if the curl extension isn't present, which I initally forgot to specifiy when I built the vagrantfile), there's a locally installed copy in the repo's /bin directory.

The VM should have nginx, PHP 5.5.x with the usual extensions, and MySQL.

The application requires the following Packagist packages (see composer.json):

- Slim (http://slimframework.com): Lightweight framework mostly used here for its routing. 
- Monolog (https://github.com/Seldaek/monolog): PSR-3 compliant logging.
- flynsarmy/slim-monolog: A log writer for Slim that uses Monolog.
- Idiorm (http://idiorm.readthedocs.org/) and Paris (http://paris.readthedocs.org/): A lightweight ORM and Acive Record implementation.
- Codeception (http://codeception.com): An automated testing suite. Codeception piggybacks on PHPUnit and other tools. 
- Phinx (http://phinx.org): Database migrations.


#### How to Run the App

- Clone the repository locally from git@bitbucket.org:michaelsmanley/spartztest.git
- From the root directory of the repository, run ```vagrant up```
- Once the VM is up and running, make sure you have 'spartztest.dev' mapped to 192.168.56.101 in your hosts file. If you're using the vagrant-hosts plugin for Vagrant, this will happen automatically on VM startup.
- ```vagrant ssh``` into the machine. Then ```cd /var/www/startztest.dev/data/etc/db/```
- You'll need to run the migrations to set up the database. ```../../../vendor/bin/phinx migrate```
- You should be able to connect to MySQL either from the command line on the VM or using your favorite tool on localhost over an SSH tunnel (SSH credentials are vagrant/vagrant; MySQL credentials are root/spartztestroot or spartztest/spartztest). Check to see that the user and city tables have been populated. 
- You should be able to go to http://spartztest.dev/visit.php to see the simple visit form I created to do the JSON POST testing. The other endpoints are all at http://spartztest.dev/v1/...

Please feel free to contact me if any of these steps are unclear or do not appear to work.


#### How to run the Tests

I've only written a few small smoke tests for the model classes. You can run them with Codeception from the VM:

```
cd /var/www/spartztest.dev/src/MSMP/Spartz
../../../vendor/bin/codecept run unit
```

One of the unit tests may fail if you already have a visit record for user id 1 and city 1 in the database.


#### Implementation

The application folder structure is based on a skeleton repo that I use for PHP projects. I've omitted the global /test and /doc/ directories that are usually there, and there are some unneeded things under /data and /web that would be more useful for a larger application.

The file ```/data/etc/db/spartztest.sql``` contains a dump of the database structure I'm using, though the schema is built via the migrations in ```/data/etc/db/migrations```, which is also where I'm storing the CSV files that were provided, for populating the database. 

I'm assuming the use of a database that has math functions, which practically means MySQL or PostgreSQL. I had wanted to use SQLite just for simplicity, but there's a bug in PDO's SQLite driver (see https://bugs.php.net/bug.php?id=64810) that doesn't allow loading of SQLite function extensions. You can see my attempt at using that in the ```/data/etc/db/sqlite_ext``` folder. Oh well. Maybe someday they'll turn that flag on. 

The DB structure is pretty simple. There are Users (id, first_name, last_name) and there are Cities (id name, state, coordinates). There are also Visits, which are a join table between User and City. Since the spec didn't say anything about recording multiple visits  by a user to a city, I assumed that once a user visited a city, that fact was idempotent. Therefore, there is a unique constraint on visits for the user_id-city_id tuple. Basically, you're checking cities off a list. If you've visited once, the city is in the list. If we had wanted to record multiple visits by a user to a city, the Visit entity would also have a timestamp and the constraint would be removed.

The entities are implemented as Paris Models, found in ```/src/MSMP/Spartz```. These have a few smoke tests but not nearly as many unit tests as I would normally like to write for model objects.

Users have a few calculated behaviors. You can get a User's full name. You can get a User's list of visisted cities. This list should only require the minimum number of queries (2), independent of the number of visits a user has.

Visits have a couple calculated behaviors. You can get a Visit's City object or User object. This is done through Paris' relationship features. If I had really used Paris' has-many-through relationship, it would probably simplify some of that model.

Cities have one calculated behavior: finding nearby cities. This is done using the Haversine formula (http://en.wikipedia.org/wiki/Haversine_formula), via the database's math functions. That magic constant "3959" is in there since we're specifying the search radius in miles. The city is not returned in its own nearby search. 

The API enpoints are implemented as Slim routes in ```\web\index.php```. I added a couple extra routes to return lists of the basic entities.

```/``` redirects to ```/v1/states```

```/v1/states``` returns a list of states as endpoint URLs for getting each state's cities.

```/v1/states/:state/cities.json``` returns a list of the state's cities in alphabetical order. If :state is not found, an empty list is returned.

```/v1/states/:state/cities/:city.json?radius=:radius``` returns the nearby cities in ascending distance order. If a state or city is not found, or if the radius is not parseable as an integer, the empty list is returned.

```/v1/users``` returns the list of users in ID order. I just did this so there'd be something useful at that URI.

```/v1/users/:uid/visits``` returns the list of cities visited by the user with id :uid. Visits are returned in database order. If :uid does not match any user id, the empty list is returned.

Posting to ```/v1/users/:uid/visits``` should work either as an AJAX post of JSON or as a plain HTTP POST of urlencoded values (city and state). The response returns 'SUCCEEDED' or 'FAILED', since there was nothing in the spec to indicate any other values. If yu would like to add visits to the database, http://spartztest.dev/visit.php provides a sample form for doing that. The form uses the AJAX method of posting new visits.

All other requests shold return a 404 response.


#### Future Enhancements

As is, this is a very small, prototype kind of app and is not really set for either high traffic or larger data sets. If this were being developed for production:

- First thing: We'd need to discuss rate of change for each of the data sets. We can probably safely assume the list of states isn't going to change on anything faster than a political timescale, so producing a list of states should be safely cacheable indefinitely. The list of cities might change on some business cycle, so we might cache city lists nightly or weekly or monthly. User records might be refreshed at any time, so we will want to talk about cache invalidation strategies for that data. Visits are similar, though I might expect us to only refresh those in batch on a daily basis. Or, they could be refreshed at any time by the users. These resfresh rates would shape our caching strategy. We would especially cache the "nearby cities" result sets.
- I'd likely be using a different ORM. Idiorm/Paris are OK for prototyping, but have some significant deficiencies when you get into anything more complex. I'd lkely switch from the Active Record pattern to a Data Mapper pattern. If I was going to stick with Idiorm/Paris, I'd fix the way I built the Visit object and use Paris' has-many-through relationship as it was intended.
- I'd be doing the right things for client-side caching of results (which would depend on knowing the refresh rate of all the data) with Etags, expiration headers, etc.
- We'd also be caching request results on the server side. This could include caching JSON requests in a cache like memcached, if it were available, or possibly caching the entire HTTP response in a caching proxy layer in front of nginx, like Varnish.
- For much larger result sets, I'd want to either add paging (with varible page sizes) or cursoring (like in the Twitter API) parameters to the requests and response data sets. We might also want to add caching advice to the responses so that API consumers could explicitly know when to refresh.
- I would normalize the API, removing .json extensions from requests and expecting API consumers to put requested data formats in their Accepts headers. We should be able to serialize the results as JSON, XML and HTML at the least. Each result set should also include links to next actions for each record. I started that kind of thing with the response that comes back to /v1/states -- what's returned is a list of API endpoints for each state.
- I would not version the API with different URL endpoints (/v1/). If there are significant differences in the responses from API endpoints from version to version, render the different responses based on the Accepts headers from the client.


#### Caveats

Unfortunately, due to other obligations, I didn't have the time to put in all of the features I would normally put into this kind of project. Here are some things I would correct if I had the time.

- Database credentials would not be hard-coded in configs. They'd be in environment variables.
- I'd test my DB migrations a bit better, particularly the rollbacks.
- Slim would have more than just the development environment set up.
- Many more unit tests, especially on the \MSMP\Spartz models. During dev I discovered a case for User::cities() that I hadn't accounted for. While I fixed it in the code, I really should have written a test for that case (empty visits).
- Codeception acceptance tests for each API endpoint. That was the whole point of including Codeception in the first place. 
- There would be Docblock comments throughout.
- There would be a Phing build file for running migrations, running tests, generating docs, etc.
- So much more security testing of bad requests, proper HTTP responses, etc. I believe I've mitigated SQL injection attacks, but I really would like to hammer this more to be more certain. A caching strategy would help with misbehaving API clients, etc.