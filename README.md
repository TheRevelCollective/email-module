E-mail module

To send an e-mail locate the "RbgEmail" service, from there you have a few methods available.

Ensure you are setting brand on all e-mail sends so the correct templates are used.

```php
// Using a predefined twig template. This can be contained within each microservice 
$message = $service->createEmail()->
    ->subject('test')
    ->setBrand($brand)
    ->template('some/twig/template.twig')
    ->setVariables(['test' => 'test'])
    ->to('target@address.com');
```

```php
//Send quick template using inky (https://get.foundation/emails/docs/index.html)
$content = <<<CONTENT
<row>
    <columns><img src="https://staff.revolutionbarsgroup.com/assets/images/where-to-be.png"
                  alt="The Place where everyone wants to be" class="thumbnail"/></columns>
</row>
<row>
    <columns>Your code is <strong>1234</strong></columns>
</row>
CONTENT;
$message = $mailer->sendInkyTemplate($brand, 'target@address.com', 'Target Name', $content);
```
