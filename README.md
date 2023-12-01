SSL will need to be generated before starting the docker. This is a self signed for now.

Example: openssl req -x509 -newkey rsa:4096 -keyout key.pem -out cert.pem -days 365

docker run -p 8443:443 login-registration
