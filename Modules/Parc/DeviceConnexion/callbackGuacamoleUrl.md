[STORPROC [!Query!]|O]
    [SWITCH [!O::Type!]|=]
        [CASE HTTPS]
            [REDIRECT]https://[!O::PortRedirectLocal!].service.abtel.fr/[/REDIRECT]
        [/CASE]
        [CASE HTTP]
            [REDIRECT]http://[!O::PortRedirectLocal!].service.abtel.fr/[/REDIRECT]
        [/CASE]
        [DEFAULT]
            [REDIRECT]https://proxy.abtel.fr/remote/#/client/[!O::GuacamoleUrl!][/REDIRECT]
        [/DEFAULT]
    [/SWITCH]
[/STORPROC]