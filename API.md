# API for website

+ [Notifications](#notifications) ([druio_notification.rest](/web/modules/custom/druio_notification/druio_notification.rest))

# Notifications

API for druio_notification entity used for website notifications.

## User notifications [/api/notifications/get-notifications]

### [GET]

Returns user notifications.

+ Parameters
    
    + `_format`: (required, string) - Format in which return result. Allowed: `json`, `xml`
    + `limit`: `5` (int) - Limit notifications to return in response.

+ Notes

    + To use this endpoint you must be login as user for which you want to get results.
    
+ Response 200 (application/json)

    ```json
    {
       "1":{
          "id":[
             {
                "value":1
             }
          ],
          "uuid":[
             {
                "value":"e3936b77-a57b-4f1f-9344-3370b2eb8536"
             }
          ],
          "langcode":[
             {
                "value":"ru"
             }
          ],
          "user_id":[
             {
                "target_id":1,
                "target_type":"user",
                "target_uuid":"dbf294ad-1d54-435b-affc-3fe6bd72dca7",
                "url":"\/user\/1"
             }
          ],
          "subject":[
             {
                "value":"First test of notifications"
             }
          ],
          "message":[
             {
                "value":"Body of notification",
                "format":null
             }
          ],
          "is_read":[
             {
                "value":false
             }
          ],
          "created":[
             {
                "value":"2018-01-22T13:50:35+00:00",
                "format":"Y-m-d\\TH:i:sP"
             }
          ]
       }
    }
    ```

## Unread count [/api/notifications/get-unread-count]

### [GET]

Returns count of unread notifications. 

+ Parameters
    
    + `_format`: (required, string) - Format in which return result. Allowed: `json`, `xml`

+ Notes

    + To use this endpoint you must be login as user for which you want to get results.
    
+ Response 200 (application/json)

    ```json
    {
       "unread_count":"1"
    }
    ```

## Unread notifications [/api/notifications/get-unread-notifications]

### [GET]

Returns all unread notifications. 

+ Parameters
    
    + `_format`: (required, string) - Format in which return result. Allowed: `json`, `xml`

+ Notes

    + To use this endpoint you must be login as user for which you want to get results.
    
+ Response 200 (application/json)

    ```json
    {
       "1":{
          "id":[
             {
                "value":1
             }
          ],
          "uuid":[
             {
                "value":"e3936b77-a57b-4f1f-9344-3370b2eb8536"
             }
          ],
          "langcode":[
             {
                "value":"ru"
             }
          ],
          "user_id":[
             {
                "target_id":1,
                "target_type":"user",
                "target_uuid":"dbf294ad-1d54-435b-affc-3fe6bd72dca7",
                "url":"\/user\/1"
             }
          ],
          "subject":[
             {
                "value":"First test of notifications"
             }
          ],
          "message":[
             {
                "value":"Body of notification",
                "format":null
             }
          ],
          "is_read":[
             {
                "value":false
             }
          ],
          "created":[
             {
                "value":"2018-01-22T13:50:35+00:00",
                "format":"Y-m-d\\TH:i:sP"
             }
          ]
       }
    }
    ```
 