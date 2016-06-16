# simple-ad-ldap
Simple php library for authenticating active directory users via ldap (currently supports proxy bind only)

### Usage
```php
$ldap = new Ldap;
$ad_info = $ldap->authenticate($username, $password);

/* $ad_info will be an object with the following format that can be used to authenticate the user however one wishes
{
"groups": [
"4",
"AD-GROUP-1",
"AD-GROUP-2",
"AD-GROUP-3",
"AD-GROUP-4",
],
"name": "Person, Some",
"email": "Some.Person@place.com"
}
*/
