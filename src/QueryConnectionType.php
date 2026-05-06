<?php

namespace Laraowl\Client;

enum QueryConnectionType: string
{
    case Read = 'read';
    case Write = 'write';
    case Unknown = 'unknown';
}
