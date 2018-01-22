# API for website

* **Notifications**

  * [Get user notifications](#get-user-notifications)
  * [Get unread count](#get-unread-count)

### Get user notifications

Returns user notifications.

* **URL**

  /api/notifications/get-notifications

* **Method:**

  `GET`
  
*  **URL Params**

   **Required:**
 
   `_format=[string]` - Format in which return values. Allowed: `json`, `xml`.

   **Optional:**
   
   `limit=[int]` - How much notifications to load. Default value is 5.

* **Data Params**

  None

* **Success Response:**

  * **Code:** 200 <br />
    **Content:** `{"1":{"id":[{"value":1}],"uuid":[{"value":"e3936b77-a57b-4f1f-9344-3370b2eb8536"}],"langcode":[{"value":"ru"}],"user_id":[{"target_id":1,"target_type":"user","target_uuid":"dbf294ad-1d54-435b-affc-3fe6bd72dca7","url":"\/user\/1"}],"subject":[{"value":"First test of notifications"}],"message":[{"value":"Body of notification","format":null}],"is_read":[{"value":false}],"created":[{"value":"2018-01-22T13:50:35+00:00","format":"Y-m-d\\TH:i:sP"}]}}`
 
* **Error Response:**

  * **Code:** 400 Bad Request<br />

* **Sample Call:**

  ```js
  fetch('/api/notifications/get-notifications?_format=json')
    .then(response => {
      return response.json();
    })
    .then(notifications => {
      console.log(notifications);
    });
  ```
* **Notes:**

  This API method required to be authenticated as user for which need to access notifications.
  
### Get unread count

Returns count of unread notifications.

* **URL**

  /api/notifications/get-unread-count

* **Method:**

  `GET`
  
*  **URL Params**

   **Required:**
 
   `_format=[string]` - Format in which return values. Allowed: `json`, `xml`.
 
* **Data Params**

  None

* **Success Response:**

  * **Code:** 200 <br />
    **Content:** `{"unread_count":"1"}`
 
* **Error Response:**

  * **Code:** 400 Bad Request<br />

* **Sample Call:**

  ```js
  fetch('/api/notifications/get-unread-count?_format=json')
    .then(response => {
      return response.json();
    })
    .then(notifications => {
      console.log(notifications);
    });
  ```
* **Notes:**

  This API method required to be authenticated as user for which need to access result.
