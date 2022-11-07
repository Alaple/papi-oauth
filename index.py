import uvicorn 
from io import open
from json import load
from requests import post, get
from fastapi import FastAPI, Request
from fastapi.responses import HTMLResponse
from fastapi.responses import RedirectResponse
from fastapi.templating import Jinja2Templates


app = FastAPI(port='2400')

access_token = ''
clientID = 'xxxx'
clientSecret = 'xxxxxx'

pages = Jinja2Templates(directory='views/pages')

@app.get('/', response_class=HTMLResponse)
async def read_item(request: Request):
    return pages.TemplateResponse('index.html', {'request': request, 'clientid': clientID})

@app.get('/github/callback', response_class=HTMLResponse)
async def read_item(request: Request, code: str = ''):
    global access_token
    requestToken = code
    
    response = post(
        f'https://github.com/login/oauth/access_token?client_id={clientID}&client_secret={clientSecret}&code={requestToken}',
        headers = {
          'accept': 'application/json'
        }
    )
    access_token = response.json()['access_token']
    return RedirectResponse('/success')
    
@app.get('/success', response_class=HTMLResponse)
async def read_item(request: Request):
    global access_token

    response = get(
        'https://api.github.com/user',
        headers = {
                'Authorization': f'token {access_token}'
        }
    )
    userData = response.json()
    with open('data.json') as f:
      data = load(f)
      result = post(
        'https://privacyapi.brandyourself.com/v1/scans',
        data = data,
        headers = {
          'Authorization': access_token
        }
      )
      print('Response:', result)
    return pages.TemplateResponse('success.html', {'request': request, 'userData': userData})


if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=2400)
