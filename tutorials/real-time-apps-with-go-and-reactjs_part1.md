# Real-time Applications with Go and ReactJS: Building a minimalistic real-time HTTP Server

## About the Series

This is the first Part of a Series of Tutorials called **Real-time Applications with Go and ReactJS**. We will build a Live Dashboard that Monitors Servers and receives Webhooks by GitlabCI (or any CI really), rendering this Data live to every Client that is connected **without any delay**.

This is what the App will look like in the End:

[![../assets/real-time-apps-with-go-and-reactjs.gif](../assets/real-time-apps-with-go-and-reactjs.gif)](../assets/real-time-apps-with-go-and-reactjs.mp4)

We gonna cover every step from an empty text editor to configuring your CI for the dashboard and deploy the docker container we will build.

**Technologies covered**

-   GoLang for the Server
    
-   HTTP/2 and Server-Sent Events (SSE)
    
-   ReactJS for the Client
    
-   Docker for Building the App and Deploying it
    

**Series Index**

-   Part 1: Basic HTTP Eventstreaming Server (*)
    
-   Part 2: Implementing the SSE Protocol Standard (not yet released)
    
-   Part 3: Building a Basic UI with ReactJS (not yet released)
    
-   Part 4: Visualizing Real-time Data with React (not yet released)
    
-   Part 5: Continuous Integration and Deployment Setup (not yet released)
    
At Reviewing the Tutorial with a Colleague @schaeferthomas he stated that "real-time" can be understood in different ways. For this Tutorial i use it in the Context of Public Networking Applications using the Definition of Oxford English:

> \[adjective\] (real-time)Computation of or relating to a system in which input data is processed within milliseconds so that it is available virtually immediately as feedback, e.g., in a missile guidance or airline booking system:*real-time signal processing*
> 
> -   Oxford Pocket Dictionary of Current English
>


## Introduction

**Prerequisites**

-   Basic Networking Knowledge
    
-   Basic Knowledge of HTTP
    
-   Some experience in Parallel or Concurrent Programming
    
-   Minimal Knowledge of GoLang
    

If you did not yet have any contact with golang, no worries. I am not gonna dive deep into the mechanics of golang. You should be able to follow even if you haven't used golang yet. But wouldn't that be a great time to make the first contact with go?

> To Follow Along without any Knowledge in golang i recommend using [gobyexample.com](https://gobyexample.com) as reference

### What we gonna build in this part

We gonna use the core `net/http` package to build a very basic real-time server that keeps connections to an endpoint `/listen` alive and takes input at `/say` to we will have the most minimal possible real-time chat app and see the awesomeness of go.

[![asciicast](https://asciinema.org/a/231626.svg)](https://asciinema.org/a/231626)

**Results at over 9000 Requests per Second**

```
Requests      [total, rate]            45005, 9001.05
Duration      [total, attack, wait]    5.000096946s, 4.99997s, 126.946µs
Latencies     [mean, 50, 95, 99, max]  132.54µs, 126.556µs, 174.755µs, 255.119µs, 3.755665ms
Success       [ratio]                  100.00%
```

This will be the base we need to build our ServerSentEvent Server for the Dashboard Application.

### Compiler

Code goes to `main.go`, so just do

`touch main.go`

You will need a golang compiler, for development i would [**recommend installing golang**](https://golang.org/dl/) and using 

`go run main.go`

Alternative: But you could also build a executable for your OS with docker (NOT recommended, go run is faster in dev)

`docker run --rm -v "$PWD":/app -w /app -e GOOS=darwin -e GOARCH=amd64 golang:1.12-alpine go build main.go`

Template for our Application:

```go
package main

import "log"

func main() {
        log.Println("Starting with Golang")
}
```

## Step 1 - Implementing a HTTP Endpoint `/say`

Starting an HTTP Server in Go is very straight forward. The [core package `net/http`](https://golang.org/pkg/net/http) provides the `ListenAndServe(address string, handler Handler)` function. The Function will run till it may receive an unrecoverable error, returning the Error message. This will be the last Statement in `func main`.

```go
log.Fatal( http.ListenAndServe(":4000", nil) )
```

To implement Handler, one possibility is using the `http.HandleFunc(urlPattern string, handlerFunction Handler)` Method. It takes a Pattern that describes the url, in our example simply `/say` and a Callback Function that is gonna execute on any request to that URL.

The Callback function receives a `ResponseWriter` interface which has a `Write([]byte])` function.

> The Write Method takes a byte array. That's great for HTTP/2 which is a binary protocol, unlike HTTP.

In our case, we want to return just a UTF-8 String. Gladly, this isn't C (even if it looks like it is) and the byte array type has a very convenient interface for converting our String to a byte array: `[]byte("string here")`.

Now we stick the Parts together:

```go
package main

import "net/http"

func sayHandler(w http.ResponseWriter, r *http.Request) {
    w.Write([]byte("Hi"))
}

func main() {
    http.HandleFunc("/say", sayHandler)
    http.ListenAndServe(":8080", nil)
}
```

Testing it with `curl`:

```bash
$ curl localhost:4000/say
Hi%
```

## Step 2 - Process Form Input

The Web and HTTP(S)(/2) is a core construct in Go, actually, it's made for Web Development and Networking.

Of course, it comes with parsing functions for URL and POST/PATCH/PUT Body.

`Request.FormValue(key String)` returns a String with the Value of the Key.

We will exchange the static "Hi" with the String we read from an Requests URL or Body.

```go
func sayHandler(w http.ResponseWriter, r *http.Request) {
 w.Write([]byte(r.FormValue("name")))

}
```

Test: `curl` (or just open it in any web browser)

```sdafsdf
$ curl localhost:4000/say -d 'name=Florian'
Florian%+
```

For our Chat Application we need another Parameter `message`.

> Usually in golang you would create a [`struct`](https://gobyexample.com/structs) now. But as said in the Intro: This is not a golang tutorial and i want to keep the code short and understandable for People not programming in golang yet.

```go
func sayHandler(w http.ResponseWriter, r *http.Request) {
    name := r.FormValue("name")
    message := r.FormValue("message")

     w.Write([]byte(name + " " + message))
}
```

## Step 3 - Implementing the Endpoint `/listen`

For the `listenHandler` we do exactly the same as we did for the `sayHandler`, but without parsing any input. 
We will instead tell the Client that the connection should be kept alive.

We create a new Handler `listenHandler` and set the **HTTP Header "Connection" to "keep-alive"** to tell the Client to not terminate the Connection.

To make sure that we are not Terminating the Connection from our side early, we wait for the Close event of the Client.

```go
//......
func listenHandler(w http.ResponseWriter, r *http.Request) {
    w.Header().Set("Connection", "keep-alive")

    select {
        case <-r.Context().Done():
            return;
    }
}
//......
func main() {
    http.HandleFunc("/listen", listenHandler)
    //......
}
```

The Arrow Syntax "<-" belongs to one of the core concepts of concurrency in golang: `channel`s, it blocks the routine till it receives data from a `channel`.

> A `channel` in go is a typed conduit that can reiceive data`channel <- data` and data can be read from `data <- channel` Writing to or Reading from a Channel BLOCKS the subroutine. [Example](https://gobyexample.com/channels)

## Step 4 - Streaming input Data to the Listeners

We have a `/say` Endpoint, receiving Data from the Client. And a `/listen` Endpoint supposed to send the Data we receive on `/say`to connected clients..

Now lets combine those. To do that, we need a new `channel` for every listener connected to send them data, we will list them in a global map of channels like so:

`var messageChannels = make(map[chan []byte]bool)`

> (FAQ) I use a Map because in the later use of the Series we will have multiple Event Types.

**listenerHandler**

Now every new Listener should create his own messageChannel:

`_messageChannel := make(chan []byte)`

And then, list it to the messageChannels Map:

`messageChannels[_messageChannel] = true`

In the Select Statement of the `listenHandler` we are **allready waiting for Data coming from the Requests Close `channel`** before we return the function and end the Connection.

Now, we create an other select case in which we are **waiting for Data from the `messageChannel`** and write the data into the ResponseWriter Stream.

```go
func listenHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Connection", "keep-alive")

	_messageChannel := make(chan []byte)
	messageChannels[_messageChannel] =  true

	for {
		select {
			case _msg := <- _messageChannel:
				w.Write(append(_msg,[]byte("\r\n")...))
				w.(http.Flusher).Flush()
			case <-r.Context().Done():
				delete(messageChannels, _messageChannel)
				return;
		}
	}
}
```

> `w.(http.Flusher).Flush()` flushs buffered data to the client explicitly. (be aware of proxy handling here if in a real world app)

**sayHandler**

In the sayHandler we will write to the `messageChannels` the listeners added. We do this in an own thread so we don't let the client wait till we channeled and processed all the data.

Since *concurrency is literally the core concept of golang*, the keyword for creating a new thread is **`go`**

```go
// sayHandler Function
    // ...
    //old: w.Write([]byte(name + " " + message))
    go func() {
        for messageChannel := range messageChannels {
            messageChannel <- []byte(name + " " + message)
        }
    }()
    w.Write([]byte("ok"))
```

> pay attention to the `}()`: We are creating and instantly invoking the function. 

## Conclusion

We just built a Realtime Chat app in 45 Lines of Go.

The `/say` endpoint processes `name` and `message`.
The `/listen` endpoint *keep-alive*s Connections and forwards input from `/say`

Test it with `curl`!

#### Disclaimer: This is not production Ready Code, for simplicity reason we omitted all the Error Checking and Input Sanitization

```go
package main

import (
	"net/http"
)

var messageChannels = make(map[chan []byte]bool)

func sayHandler(w http.ResponseWriter, r *http.Request) {
	name := r.FormValue("name")
	message := r.FormValue("message")

	go func() {
		for messageChannel := range messageChannels {
			messageChannel <- []byte(name + " " + message)
		}
	}()

	w.Write([]byte("ok."))
}

func listenHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Connection", "keep-alive")

	_messageChannel := make(chan []byte)
	messageChannels[_messageChannel] =  true

	for {
		select {
			case _msg := <- _messageChannel:
				w.Write(append(_msg,[]byte("\r\n")...))
				w.(http.Flusher).Flush()
			case <-r.Context().Done():
				delete(messageChannels, _messageChannel)
				return;
		}
	}
}

func main() {
	http.HandleFunc("/say", sayHandler)
	http.HandleFunc("/listen", listenHandler)

	print("started")
	http.ListenAndServe(":4000", nil)
}
```

In the next Part we will built the Server-Sent-Event Protocol ourself and connect it to a JavaScript Client for Realtime Browser Action. Thanks for Reading.
