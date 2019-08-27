<?php

return [

    /*
     * Set trusted proxy IP addresses.
     *
     * Both IPv4 and IPv6 addresses are
     * supported, along with CIDR notation.
     *
     * The "*" character is syntactic sugar
     * within TrustedProxy to trust any proxy
     * that connects directly to your server,
     * a requirement when you cannot know the address
     * of your proxy (e.g. if using ELB or similar).
     *
     */
    'proxies' => [ // [<ip addresses>,], '*'
        // Cloudflare
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '104.16.0.0/12',
        '108.162.192.0/18',
        '131.0.72.0/22',
        '141.101.64.0/18',
        '162.158.0.0/15',
        '172.64.0.0/13',
        '173.245.48.0/20',
        '188.114.96.0/20',
        '190.93.240.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        // Docker local and prod networks
        '172.21.0.0/16',
        // Local
        //'172.21.0.1',
        // Production
        //'172.19.0.2',
        //'172.19.0.3',
        //'172.19.0.4',
        //'172.19.0.5',
        //'172.19.0.6',
        //'172.19.0.7',
    ],

    /*
     * To trust one or more specific proxies that connect
     * directly to your server, use an array of IP addresses:
     */
    # 'proxies' => ['192.168.1.1'],

    /*
     * Or, to trust all proxies that connect
     * directly to your server, use a "*"
     */
    # 'proxies' => '*',

    /*
     * Which headers to use to detect proxy related data (For, Host, Proto, Port)
     * 
     * Options include:
     * 
     * - Illuminate\Http\Request::HEADER_X_FORWARDED_ALL (use all x-forwarded-* headers to establish trust)
     * - Illuminate\Http\Request::HEADER_FORWARDED (use the FORWARDED header to establish trust)
     * 
     * @link https://symfony.com/doc/current/deployment/proxies.html
     */
    'headers' => Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO,


];
