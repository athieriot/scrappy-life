<?php
namespace Scrappy\Model;

use DateTime;
use MongoDB\BSON\Persistable;
use MongoDB\BSON\UTCDateTime;

/**
 * Class VDMPost represent a post from the VDM website
 *
 * Persistable to MongoDB with extra fields
 *
 */
class VDMPost implements Persistable {

    const DISPLAY_DATE_FORMAT = "Y-m-d\TH:i:s\Z";

    private $content;

    private $author;

    private $date;

    public function __construct(string $content, string $author, DateTime $date)
    {
        $this->content = $content;
        $this->author = $author;
        $this->date = $date;
    }

    public function bsonSerialize()
    {
        return [
            '_id' => sha1($this->content),
            'content' => $this->content,
            'author' => $this->author,
            'date' => $this->date->format(self::DISPLAY_DATE_FORMAT),
            'timestamp' => new UTCDateTime($this->date->getTimestamp() * 1000)
        ];
    }

    public function bsonUnserialize(array $data)
    {
        //TODO: To implement when we will read from the Database
        return null;
    }
}