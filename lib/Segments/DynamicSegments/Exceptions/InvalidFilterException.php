<?php

namespace MailPoet\Segments\DynamicSegments\Exceptions;

use MailPoet\InvalidStateException;

class InvalidFilterException extends InvalidStateException {

  const MISSING_TYPE = 1;
  const INVALID_TYPE = 2;
  const MISSING_ROLE = 3;
  const MISSING_ACTION = 4;
  const MISSING_NEWSLETTER_ID = 5;
  const MISSING_CATEGORY_ID = 6;
  const MISSING_PRODUCT_ID = 7;

};
