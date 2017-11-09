# f3-sms
A simple f3 plugin to send sms with Mailup.it service

Initializing smsClient

```
$MAILUP_LIST_ID = '1';
$MAILUP_LIST_GUID = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';
$MAILUP_CAMPAIGN_CODE = 'Code verify';

$sms = new SMS($MAILUP_LIST_ID, $MAILUP_LIST_GUID, $MAILUP_CAMPAIGN_CODE);

```
Logging with list secret Or
```  						   
$sms->logOnWithToken('m00000', 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee');
```

Log On With Password and username

```
$sms->logOnWithPassword('m00000', 'password');
```

and send message

```
$sent = $sms->sendSms('+39.....', 'text of message','sender');
```
