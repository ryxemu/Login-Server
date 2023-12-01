package main

import (
	"fmt"
	"log"
	"net/http"
)

func main() {
	http.HandleFunc("/google-login", handleGoogleLogin)
	http.HandleFunc("/google-callback", handleGoogleCallback)

	fmt.Println("Server started on port 8080")
	log.Fatal(http.ListenAndServe(":8080", nil))
}
