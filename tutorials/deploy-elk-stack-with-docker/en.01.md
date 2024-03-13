---
SPDX-License-Identifier: MIT
path: "/tutorials/deploy-elk-stack-with-docker"
slug: "deploy-elk-stack-with-docker"
date: "2024-0-0"
title: "Deploy your own ELK stack using Docker Compose"
short_description: ""
tags: [ "Ubuntu", "Docker", "Docker Compose", "ELK" ]
author: "Alexandru Popescu"
author_link: "https://github.com/Blue25GD"
author_img: "https://avatars3.githubusercontent.com/u/113828070"
author_description: "Creating awesome web products"
language: "en"
available_languages: [ "en" ]
header_img: "header-1"
cta: "cloud"
---

## Introduction

In this article you will learn how to install an ELK stack using Docker Compose on an Ubuntu (version 22.04) server.

**Prerequisites**

* An Ubuntu server running version 22.04 or later
* SSH Access to that server
* Basic knowledge
  of [Docker](https://docker.com), [Docker Compose](https://docs.docker.com/compose), [ElasticSearch](https://elastic.co)
  and YAML

**Terminology**

* The username of your server: `<your-username>`
* The ip or hostname of your server: `<your-server>`
* A password of your choice for the elastic user: `<your-elastic-password>`

## Step 1 - Install Docker Compose (Optional)

You may skip this step if you have already installed Docker Compose on your server. First, SSH into your server using
the following command:

```shell
ssh <your-username>@<your-server>
```

For this tutorial, my server will be named `server` and my username will be `alex`. The output should look something
like this:

![image1](images/image2.png)

After this, make sure to update apt packages and install cURL:

```shell
sudo apt-get update && sudo apt-get install curl -y
```

![image2](images/image6.png)

After making sure curl is installed, we can use the quick install script provided by Docker to install Docker as well as
Docker Compose:

```shell
curl https://get.docker.com | sh
```

This command will download the script from get.docker.com and "pipe" it to sh (It will feed the downloaded script to sh
which will execute that script and install Docker).
The last thing we can do is add ourselves to the Docker group so that we don’t need to use sudo everytime we use the
docker command.

![image3](images/image3.png)

```shell
sudo usermod -aG docker <your-username>
```

Make sure to log out and log in again to apply changes.

## Step 2 - Create docker-compose.yaml

The docker-compose.yaml file will be used to declare all the infrastructure for the ELK stack. It is used to create
many containers using a single command. Create a new folder on your server and create a `docker-compose.yaml` file in
it:

```shell
mkdir elk-stack && cd elk-stack && touch docker-compose.yaml
```

Add the following content to the docker-compose.yaml file:

```yaml
version: "3"
services:
  elasticsearch:
    image: docker.elastic.co/elasticsearch/elasticsearch:8.12.1
    # give the container a name
    # this will also set the container's hostname as elasticsearch
    container_name: elasticsearch
    environment:
      - discovery.type=single-node
      - cluster.name=elasticsearch
      - bootstrap.memory_lock=true
      # limits elasticsearch to 1 GB of RAM
      - ES_JAVA_OPTS=-Xms1g -Xmx1g
      # The password for the 'elastic' user
      - ELASTIC_PASSWORD=${ELASTIC_PASSWORD}
      - xpack.security.http.ssl.enabled=false
```

This will create a container named elasticsearch when running Docker Compose. We are currently missing an element
though, the `.env` file. Let’s create it now:

```shell
# .env
ELASTIC_PASSWORD=<your-elastic-password>
```

The `.env` file is used to store secrets like passwords and API tokens to remove them from your configuration or code.
Docker Compose automatically recognises the `.env` file and replaces variables like `${MY_VARIABLE}` with the variable
from `.env`.

### Step 2.1 - Start elasticsearch (Optional)

The next step is to start the elasticsearch container. You can do this using the `docker compose` command:

```shell
docker compose up -d
```

![image4](images/image1.png)

We can check that everything is working using the `docker ps` command.

## Step 3 - Kibana

Next up on our list is Kibana. Kibana can be used to visualize the data from Elasticsearch with beautiful graphs and
dashboards. Take a look.

![image5](images/image13.png)

We will of course need to update the `docker-compose.yaml` file, add the following:

```yaml
# continue in services:
kibana:
  image: docker.elastic.co/kibana/kibana:8.12.1
  container_name: kibana
  ports:
    - 5601:5601
  environment:
    # remember the container_name for elasticsearch?
    # we use it here to access that container
    - ELASTICSEARCH_URL=http://elasticsearch:9200
    - ELASTICSEARCH_HOSTS=http://elasticsearch:9200
    - ELASTICSEARCH_USERNAME=kibana_system
    - ELASTICSEARCH_PASSWORD=${KIBANA_PASSWORD}
    # Change this to true if you want to sent
    # telemetry data to kibana developers
    - TELEMETRY_ENABLED=false
```

Also add the `KIBANA_PASSWORD` variable to the .env file:

```shell
# .env
ELASTIC_PASSWORD=...
KIBANA_PASSWORD=<your-kibana-password>
```

We also need to add another container that will start before the other containers and configure the passwords,
place it at the top of your file in services:

```yaml
setup:
  image: docker.elastic.co/elasticsearch/elasticsearch:8.12.1
  environment:
    - ELASTIC_PASSWORD=${ELASTIC_PASSWORD}
    - KIBANA_PASSWORD=${KIBANA_PASSWORD}
  container_name: setup
  command:
    - bash
    - -c
    - |
      echo "Waiting for Elasticsearch availability";
      until curl -s http://elasticsearch:9200 | grep -q "missing authentication credentials"; do sleep 30; done;
      echo "Setting kibana_system password";
      until curl -s -X POST -u "elastic:${ELASTIC_PASSWORD}" -H "Content-Type:
      application/json" http://elasticsearch:9200/_security/user/kibana_system/_password -d "{\"password\":
      \"${KIBANA_PASSWORD}\"}" | grep -q "^{}"; do sleep 10; done;
      echo "All done!";
```

You can now run docker compose to start everything up:

```shell
docker compose up -d
```

![image6](images/image12.png)

You can now go to Kibana on a web browser by entering <your_server>:5601 in the URL bar.
Use the username `elastic` and the password you chose earlier in the .env file:

![image7](images/image4.png)

If you have this screen when logging in, click on "explore on my own".

![image8](images/image7.png)

You should now be able to access the Kibana homepage. It looks like this:

![image9](images/image9.png)

## Step 4 - Logstash

Now it’s time to add the final piece of the puzzle, Logstash. Logstash can analyse logs from your application(s) and
feeds the analysed logs to elasticsearch. We will need to modify the docker-compose.yaml file:

```yaml
# continue in services:
logstash:
  image: docker.elastic.co/logstash/logstash:8.12.1
  container_name: logstash
  command:
    - /bin/bash
    - -c
    - |
      cp /usr/share/logstash/pipeline/logstash.yml /usr/share/logstash/config/logstash.yml
      echo "Waiting for Elasticsearch availability";
      until curl -s http://elasticsearch:9200 | grep -q "missing authentication credentials"; do sleep 1; done;
      echo "Starting logstash";
      /usr/share/logstash/bin/logstash -f /usr/share/logstash/pipeline/logstash.conf
  environment:
    - xpack.monitoring.enabled=false
    - ELASTIC_USER=elastic
    - ELASTIC_PASSWORD=${ELASTIC_PASSWORD}
    - ELASTIC_HOSTS=http://elasticsearch:9200
  volumes:
    - ./logstash.conf:/usr/share/logstash/pipeline/logstash.conf
```

Setting up Logstash is a bit more complicated; you need one additional configuration file, `logstash.conf`. Logstash works
on something called a "pipeline". It’s a file explaining what Logstash should do (where do logs come from, how to
analyse the logs where to send them). The pipeline will be in the file logstash.conf.
This is one of the most basic pipelines you could have:

```text
input {
    file {
        path => "/var/log/dpkg.log"
        start_position => "beginning"
    }
}

filter { }

output {
    elasticsearch {
    hosts => "${ELASTIC_HOSTS}"
    user => "elastic"
    password => "${ELASTIC_PASSWORD}"
    index => "logstash-%{+YYYY.MM.dd}"
    }
    stdout { }
}
```

It’s pretty self explanatory, it takes a file as input (in this case `/var/log/dpkg.log`) and outputs to Elasticsearch
and
`stdout`. Put this in your logstash.conf file.
You can now start Logstash using the following command:

```shell
docker compose up -d
```

![image10](images/image8.png)

You can now access Logstash from Kibana. You will need to create a logstash data view first. Go on the discover page of
"Analytics". You should see something like this:

![image11](images/image10.png)

Create your data view by clicking on the "Create data view" button:

![image12](images/image5.png)

You should now be able to see logs coming from Logstash:

![image13](images/image11.png)

## Step 5 - Destroy the stack

Lastly, to stop the stack and remove the containers, run the following command:

```shell
docker compose down
```

![image14](images/image14.png)

## Conclusion

That’s it! You should have a working ELK stack running with Docker Compose. Next steps would be to add log exporters such as Filebeat or check out the [official documentation](https://www.elastic.co/guide/index.html).

##### License: MIT

<!--

Contributor's Certificate of Origin

By making a contribution to this project, I certify that:

(a) The contribution was created in whole or in part by me and I have
    the right to submit it under the license indicated in the file; or

(b) The contribution is based upon previous work that, to the best of my
    knowledge, is covered under an appropriate license and I have the
    right under that license to submit that work with modifications,
    whether created in whole or in part by me, under the same license
    (unless I am permitted to submit under a different license), as
    indicated in the file; or

(c) The contribution was provided directly to me by some other person
    who certified (a), (b) or (c) and I have not modified it.

(d) I understand and agree that this project and the contribution are
    public and that a record of the contribution (including all personal
    information I submit with it, including my sign-off) is maintained
    indefinitely and may be redistributed consistent with this project
    or the license(s) involved.

Signed-off-by: [Alexandru Popescu (alexandru.popescu.fr@icloud.com)]

-->
