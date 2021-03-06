<?php

namespace Keyword\Model;

use Keyword\Model\Base\KeywordQuery as BaseKeywordQuery;

/**
 * Skeleton subclass for performing query and update operations on the 'keyword' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class KeywordQuery extends BaseKeywordQuery
{

    public static function getKeywordByCode($code)
    {
        return self::create()->findOneByCode($code);
    }

} // KeywordQuery
