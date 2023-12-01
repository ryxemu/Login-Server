package main

import (
	"fmt"
	"log"
	"net/http"
)

func main() {
	http.HandleFunc("/google-login", handleGoogleLogin)
	http.HandleFunc("/google-callback", handleGoogleCallback)

	fmt.Println("Server started on port 8443")
	log.Fatal(http.ListenAndServe(":8443", nil))
}
