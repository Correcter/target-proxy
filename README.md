#### Proxy for working with target.my.com

##### The application is a proxying single-threaded gateway for redirecting requests to third-party services.

### The principle of interaction:
- The proxy is located at: **http://target-proxy.icontext.ru/**;
- URI calls must be specified directly after the address: **http://target-proxy.icontext.ru ...**, with the addition of the following parameters;
- To authenticate requests to the proxy server, the header **Authorization:** Bearer token generated on the server **JWT tokens** is used, in accordance with the user rights of the proxy server;
- Each client request must contain the required parameters: **GET/POST**: `client` or **HEAD** `X-TARGET-CLIENT` And **GET/POST**: `agency` or **HEAD** `X-TARGET-AGENCY" (approx. **?agency=...&client=...** );
- To request the **AGENCY** methods, you need papameters: **GET/POST**: `agency` or **HEAD** `X-TARGET-AGENCY`
- Proxy accepts all basic types of HTTP requests: **GET**, **POST**, **PUT**, **DELETE**;
- Request/response format - **json**


#### Example of a request for an agency:

GET/POST/DELETE /api/v2/campaigns.json HTTP/1.1 \
Host: target-proxy.icontext.ru \
token: {proxy_token} \
agency: {agency_name} \

#### Example of a request for a client:

GET/POST/DELETE /api/v2/campaigns.json HTTP/1.1 \
Host: target-proxy.icontext.ru \
token: {proxy_token} \
client: {client_name} \
agency: {agency_name}

**http://target-proxy.icontext.ru/api/v2/campaigns.json?token=token&client=client_name**

**Sample response, if successful**

HTTP/1.1 200 OK \
Content-Type: application/json; charset=UTF-8

 ```json
  count: 2516,
  items: [
      {  
          package_id: 23,
          id: 3784532,
          name: "cat_sumki2_60,61,62,63,64_ Beauty and self-care"
      },
      {
package_id: 23,
id: 3784533,
name: "cat_sumki2_40,41,42,43,44_wear, shoes and accessories"
      },
      {
package_id: 23,
id: 3784534,
name: "cat_sumki2_45,46,47,48,49_wear, shoes and accessories"
      },
 ```
 
 **Possible proxy request errors:**

- If you have not specified under which client (**?client=**) you want to access the proxy server, you will receive an error like: 
    ``` HTTP/1.1 403 HTTP FORBIDDEN```
    ```json 
    {
         error: "The user is not received!"
    }
    ```
  
  - In case you didn't specify the method (**/api/v2/method.json**), get the error:
`` HTTP/1.1 403 HTTP FORBIDDEN``
    ```json 
    {
        error: "Method is not defined!"
    }
    ``
- The policy of access to the methods of the service through a proxy, assumes the presence of allowed 
    and prohibited methods for each client token. If you do not have access, there will be an error:
    ``` HTTP/1.1 403 HTTP FORBIDDEN```
    ```json 
      {
          error: "Method is not allowed!"
      }
    ```  
  - The proxy server assumes sequential processing of responses from the requested service,
therefore, do not request content in multiple streams. Otherwise, you will get an error:
    
    ``` HTTP/1.1 429 TOO MANY REQUESTS```
    ```json 
      {
          error: "Too Many Requests!"
      }
    ```

    **Errors when working with the requested resources:**
    
  - If authorization data could not be obtained for requests to the service you specified, you will receive one of the errors:

`` HTTP/1.1 403 HTTP NOT FOUND`` 
    ```json 
      {
          error: "An error occurred during the tokenizer request!"
      }
    ```
    
     **or**
   
    ``` HTTP/1.1 204 HTTP NO CONTENT``` 
    ```json 
         {
             error: "The token request was not completed!"
         }
    ```

     **or**
    
    ``` HTTP/1.1 204 HTTP NO CONTENT``` 
    ```json 
         {
            error: "Tokenizer sents invalid json"
         }
    ```
    **or**
        
    ``` HTTP/1.1 415 HTTP UNSUPPORTED MEDIA TYPE``` 
    ```json 
         {
             error: "Tokenizer did not return a token"
         }
    ```
    
   - If the proxy server failed to receive a response from the service within the allotted time, then you will receive an error
that may be associated with incorrect request parameters (GET, POST, PUT, HEAD, DELETE):
   
    ``` HTTP/1.1 504 HTTP GATEWAY TIMEOUT``` 
    ```json 
        {
             error: "The service is not responding"
        }    
    ``
- Another error may be received if you incorrectly specified the requested method or path to the API of the service: 
    
    ``` HTTP/1.1 404 HTTP NOT FOUND``` 
    ```json 
        {
             error: "Invalid request path or method"
        }    
    ``

- If the data you are requesting does not meet the expected response from the service, you will receive an error: 
     
     ``` HTTP/1.1 400 HTTP BAD REQUEST``` 
     ```json 
         {
              error: "The request did not return valid data"
         }    
     ```
 
#### Proxy allows you to get an access token for the registry with a request of the following type:
 
 **https://target-proxy.icontext.ru/RClientsNet?client_name=...&token=...**
 
 GET /RClientsNet HTTP/1.1 \
 Host: target-proxy.icontext.ru \
 token: {registratura_token} \
 client_name: {client_name}
 
 ...
