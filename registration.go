package main

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"log"
	"net/http"

	"golang.org/x/oauth2"
	"golang.org/x/oauth2/google"

	_ "github.com/mattn/go-sqlite3"
)

var db *sql.DB

func main() {
	var err error
	db, err = sql.Open("sqlite3", "test.db")
	if err != nil {
		log.Fatal("Failed to open database:", err)
	}
	defer db.Close()

	_, err = db.Exec(`CREATE TABLE IF NOT EXISTS users (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		google_id TEXT,
		email TEXT,
		name TEXT
	)`)
	if err != nil {
		log.Fatal("Failed to create table:", err)
	}

	http.HandleFunc("/google-login", handleGoogleLogin)
	http.HandleFunc("/google-callback", handleGoogleCallback)

	fmt.Println("Server started on port 8080")
	log.Fatal(http.ListenAndServe(":8080", nil))
}

func handleGoogleLogin(w http.ResponseWriter, r *http.Request) {
	googleOauthConfig := &oauth2.Config{
		ClientID:     "YOUR_GOOGLE_CLIENT_ID",
		ClientSecret: "YOUR_GOOGLE_CLIENT_SECRET",
		RedirectURL:  "http://localhost:8080/google-callback",
		Scopes:       []string{"profile", "email"},
		Endpoint:     google.Endpoint,
	}

	url := googleOauthConfig.AuthCodeURL("state-token", oauth2.AccessTypeOffline)
	http.Redirect(w, r, url, http.StatusTemporaryRedirect)
}

func handleGoogleCallback(w http.ResponseWriter, r *http.Request) {
	googleOauthConfig := &oauth2.Config{
		ClientID:     "YOUR_GOOGLE_CLIENT_ID",
		ClientSecret: "YOUR_GOOGLE_CLIENT_SECRET",
		RedirectURL:  "http://localhost:8080/google-callback",
		Scopes:       []string{"profile", "email"},
		Endpoint:     google.Endpoint,
	}

	state := r.FormValue("state")
	code := r.FormValue("code")
	token, err := googleOauthConfig.Exchange(r.Context(), code)
	if err != nil {
		http.Error(w, "Failed to exchange token", http.StatusBadRequest)
		return
	}

	client := googleOauthConfig.Client(r.Context(), token)
	response, err := client.Get("https://www.googleapis.com/oauth2/v2/userinfo")
	if err != nil {
		http.Error(w, "Failed to get user info", http.StatusInternalServerError)
		return
	}
	defer response.Body.Close()

	var userInfo struct {
		ID    string `json:"id"`
		Email string `json:"email"`
		Name  string `json:"name"`
	}
	err = json.NewDecoder(response.Body).Decode(&userInfo)
	if err != nil {
		http.Error(w, "Failed to decode user info", http.StatusInternalServerError)
		return
	}

	// Check if user already exists in the database
	var count int
	err = db.QueryRow("SELECT COUNT(id) FROM users WHERE google_id = ?", userInfo.ID).Scan(&count)
	if err != nil {
		http.Error(w, "Failed to check user", http.StatusInternalServerError)
		return
	}

	if count > 0 {
		// User exists, log in
		fmt.Fprintf(w, "User already exists, logged in as %s", userInfo.Name)
	} else {
		// Insert new user into the database
		_, err := db.Exec("INSERT INTO users (google_id, email, name) VALUES (?, ?, ?)", userInfo.ID, userInfo.Email, userInfo.Name)
		if err != nil {
			http.Error(w, "Failed to insert user", http.StatusInternalServerError)
			return
		}
		fmt.Fprintf(w, "New user registered: %s", userInfo.Name)
	}
}
