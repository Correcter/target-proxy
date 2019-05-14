#### Прокси для работы с target.my.com

##### Приложение представляет собой проксирующий однопоточный шлюз, для перенаправления запросов на сторонние сервисы.

### Принцип взаимодействия:
- Прокси находится по адресу: **http://target-proxy.icontext.ru/**;
- URI вызовы необходимо указывать непосредственно после адреса: **http://target-proxy.icontext.ru...**, с добавлением нижеследующих параметров;
- Для аутентификации запросов в прокси-сервер, используются: **GET** параметр **?token=** **ИЛИ** заголовок **X-AUTH-TOKEN** (токен), выдаваемый в соответствии с правами пользователя прокси сервером;
- Каждый клиентский запрос должен содержать обязательные  параметры: **GET/POST**: ``client`` или **HEAD** ``X-TARGET-CLIENT``  И  **GET/POST**: ``agency`` или **HEAD** ``X-TARGET-AGENCY``  (прим. **?agency=...&client=...&token=...** );
- Для запроса к методам **АГЕНТСТВА**, нужны папаметры: **GET/POST**: ``agency`` или **HEAD** ``X-TARGET-AGENCY``
- Прокси принимает все основыне типы HTTP запросов: **GET**, **POST**, **PUT**, **DELETE**;
- Ответ от прокси-сервера будет приходить исключительно в **json**


#### Пример запроса для агентства:

GET/POST/DELETE /api/v2/campaigns.json HTTP/1.1 \
Host: target-proxy.icontext.ru \
token: {proxy_token} \
agency: {agency_name} \

#### Пример запроса для клиента:

GET/POST/DELETE /api/v2/campaigns.json HTTP/1.1 \
Host: target-proxy.icontext.ru \
token: {proxy_token} \
client: {client_name} \
agency: {agency_name}

**http://target-proxy.icontext.ru/api/v2/campaigns.json?token=token&client=client_name**

**Пример ответа, в случае успешного выполнения**

HTTP/1.1 200 OK \
Content-Type: application/json; charset=UTF-8

 ```json
  count: 2516,
  items: [
      {  
          package_id: 23,
          id: 3784532,
          name: "cat_sumki2_60,61,62,63,64_Красота и уход за собой"
      },
      {
          package_id: 23,
          id: 3784533,
          name: "cat_sumki2_40,41,42,43,44_Одежда, обувь и аксессуары"
      },
      {
          package_id: 23,
          id: 3784534,
          name: "cat_sumki2_45,46,47,48,49_Одежда, обувь и аксессуары"
      },
 ```
 
 **Возможные ошибки запросов к прокси:**
  
  - Если вы не указали под каким клиентом (**?client=**) хотите обращаться к прокси-серверу, вы получите ошибку вида: 
    ``` HTTP/1.1 403 HTTP FORBIDDEN```
    ```json 
    {
         error: "The user is not received!"
    }
    ```
  
  - В случае, если вы не указали метод (**/api/v2/method.json**), получите ошибку:
    ``` HTTP/1.1 403 HTTP FORBIDDEN```
    ```json 
    {
        error: "Method is not defined!"
    }
    ```
  - Политика доступа к методам сервиса через прокси, предполагает наличие разрешенных 
    и запрещенных методов на каждый токен клиента. В случае отсутствия у вас доступа, будет ошибка:
    ``` HTTP/1.1 403 HTTP FORBIDDEN```
    ```json 
      {
          error: "Method is not allowed!"
      }
    ```  
  - Прокси-сервер предполагает последовательную обработку ответов от запрашиваемого сервиса, 
    поэтому, не запрашивайте контент в несколько потоков. Иначе, вы получите ошибку:
    
    ``` HTTP/1.1 429 TOO MANY REQUESTS```
    ```json 
      {
          error: "Too Many Requests!"
      }
    ```

    **Ошибки при работе с запрашиваемыми ресурсами:**
    
  - Если не удалось получить авторизационные данные, для запросов в указанный вами сервис, вы получите одну из ошибок:
  
    ``` HTTP/1.1 403 HTTP NOT FOUND``` 
    ```json 
      {
          error: "An error occurred during the tokenizer request!"
      }
    ```
    
     **или**
   
    ``` HTTP/1.1 204 HTTP NO CONTENT``` 
    ```json 
         {
             error: "The token request was not completed!"
         }
    ```

     **или**
    
    ``` HTTP/1.1 204 HTTP NO CONTENT``` 
    ```json 
         {
            error: "Tokenizer sents invalid json"
         }
    ```
    **или**
        
    ``` HTTP/1.1 415 HTTP UNSUPPORTED MEDIA TYPE``` 
    ```json 
         {
             error: "Tokenizer did not return a token"
         }
    ```
    
   - Если прокси-серверу не удалось за отведенное время получить ответ от сервиса, то вы получите ошибку, 
     которая может быть связана с неверными параметрами запроса (GET, POST, PUT, HEAD, DELETE):
   
    ``` HTTP/1.1 504 HTTP GATEWAY TIMEOUT``` 
    ```json 
        {
             error: "The service is not responding"
        }    
    ```
   - Еще одна ошибка может быть получена, если вы неверно указали запрашиваемый метод или путь к АПИ сервиса: 
    
    ``` HTTP/1.1 404 HTTP NOT FOUND``` 
    ```json 
        {
             error: "Invalid request path or method"
        }    
    ```
 
   - Если запрашиваемые вами данные не отвечают ожидаемому ответу со стороны сервиса, вы получите ошибку: 
     
     ``` HTTP/1.1 400 HTTP BAD REQUEST``` 
     ```json 
         {
              error: "The request did not return valid data"
         }    
     ```
 
#### Proxy позволяет получать access токен для регистратуры запросом следующего вида:
 
 **https://target-proxy.icontext.ru/RClientsNet?client_name=...&token=...**
 
 GET /RClientsNet HTTP/1.1 \
 Host: target-proxy.icontext.ru \
 token: {registratura_token} \
 client_name: {client_name}
 
 ...
 