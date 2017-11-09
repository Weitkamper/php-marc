<?php

namespace Scriptotek\Marc;

use Scriptotek\Marc\Exceptions\UnknownRecordType;
use Scriptotek\Marc\Fields\Classification;
use Scriptotek\Marc\Fields\Isbn;
use Scriptotek\Marc\Fields\Person;
use Scriptotek\Marc\Fields\Subject;
use Scriptotek\Marc\Fields\SubjectInterface;
use Scriptotek\Marc\Fields\Title;

class BibliographicRecord extends Record
{
    /**
     * @var array List of properties to be included when serializing the record using the `toArray()` method.
     */
    public $properties = ['id', 'isbns', 'title', 'creators', 'subjects', 'classifications'];

    /**
     * Get the descriptive cataloging form value from LDR/18. Returns any of
     * the constants Marc21::NON_ISBD, Marc21::AACR2, Marc21::ISBD_PUNCTUATION_OMITTED,
     * Marc21::ISBD_PUNCTUATION_INCLUDED, Marc21::NON_ISBD_PUNCTUATION_OMITTED
     * or Marc21::UNKNOWN_CATALOGING_FORM.
     *
     * @property Isbn[] isbns
     * @property string title
     * @property SubjectInterface[] subjects
     *
     * @return string
     * @throws UnknownRecordType
     */
    public function getCatalogingForm()
    {
        $leader = $this->record->getLeader();
        return substr($leader, 18, 1);
    }

    /*************************************************************************
     * Helper methods for specific fields. Each of these are supported by
     * a class in src/Fields/
     *************************************************************************/

    /**
     * Get an array of the 020 fields as `Isbn` objects.
     *
     * @return Isbn[]
     */
    public function getIsbns()
    {
        return Isbn::get($this);
    }

    /**
     * Get the 245 field as a `Title` object. Returns null if no such field was found.
     *
     * @return Title
     */
    public function getTitle()
    {
        return Title::get($this);
    }

    /**
     * Get an array of the 6XX fields as `SubjectInterface` objects, optionally
     * filtered by vocabulary and/or tag.
     *
     * @param string $vocabulary
     * @param string|string[] $tag
     * @return SubjectInterface[]
     */
    public function getSubjects($vocabulary = null, $tag = null)
    {
        $tag = is_null($tag) ? [] : (is_array($tag) ? $tag : [$tag]);

        return array_values(array_filter(Subject::get($this), function (SubjectInterface $subject) use ($vocabulary, $tag) {
            $a = is_null($vocabulary) || $vocabulary == $subject->getVocabulary();
            $b = empty($tag) || in_array($subject->getType(), $tag);

            return $a && $b;
        }));
    }

    /**
     * Get an array of the 080, 082, 083, 084 fields as `Classification` objects, optionally
     * filtered by scheme and/or tag.
     *
     * @param string $scheme
     * @return Classification[]
     */
    public function getClassifications($scheme = null)
    {
        return array_values(array_filter(Classification::get($this), function ($classifications) use ($scheme) {
            $a = is_null($scheme) || $scheme == $classifications->getScheme();

            return $a;
        }));
    }

    /**
     * Get an array of the 100 and 700 fields as `Person` objects, optionally
     * filtered by tag.
     *
     * @param string|string[] $tag
     * @return Person[]
     */
    public function getCreators($tag = null)
    {
        $tag = is_null($tag) ? [] : (is_array($tag) ? $tag : [$tag]);

        return array_values(array_filter(Person::get($this), function (Person $person) use ($tag) {
            return empty($tag) || in_array($person->getType(), $tag);
        }));
    }
}
