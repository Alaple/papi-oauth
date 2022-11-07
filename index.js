/*  EXPRESS */
const express = require('express');
const app = express();
const dataJson = require('./data.json')

app.set('view engine', 'ejs');
let access_token = "";

app.get('/', function(req, res) {
  res.render('pages/index',{client_id: clientID});
});

const port = process.env.PORT || 2400;
app.listen(port , () => console.log('App listening on port ' + port));

// Import the axios library, to make HTTP requests
const axios = require('axios')
// This is the client ID and client secret that you obtained
// while registering on github app
const clientID = 'xxx'
const clientSecret = 'xxxxxxxxxxx'

// Declare the callback route
app.get('/github/callback', (req, res) => {

  // The req.query object has the query params that were sent to this route.
  const requestToken = req.query.code
  
  axios({
    method: 'post',
    url: `https://github.com/login/oauth/access_token?client_id=${clientID}&client_secret=${clientSecret}&code=${requestToken}`,
    // Set the content type header, so that we get the response in JSON
    headers: {
         accept: 'application/json'
    }
  }).then((response) => {
    access_token = response.data.access_token
    res.redirect('/success');
  })
})

app.get('/success', function(req, res) {
  axios({
    method: 'get',
    url: `https://api.github.com/user`,
    headers: {
      Authorization: 'token ' + access_token
    }
  }).then((response) => {
    access_token = response.data.access_token
    res.render('pages/success',{ userData: response.data });
    try {
        const response = axios.post('https://privacyapi.brandyourself.com/v1/scans',dataJson,
        {
            headers: {
            Authorization: access_token
            }
        })

        console.log('Response:', response.data)
    } catch (error) {
        console.error(error)
    }
    
  })
});