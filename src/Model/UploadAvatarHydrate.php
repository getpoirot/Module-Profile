<?php
namespace Module\Profile\Model;

use Poirot\Psr7\UploadedFile;
use Poirot\Std\Exceptions\exUnexpectedValue;
use Poirot\Std\Hydrator\aHydrateEntity;


class UploadAvatarHydrate
    extends aHydrateEntity
{
    /** @var UploadedFile */
    protected $pic;
    protected $asPrimary = false;


    // Setter Options:

    function setPic($uploadFile)
    {
        $this->pic = $uploadFile;
    }

    function setAsPrimary($flag)
    {
        $this->asPrimary = $flag;
    }


    // Getter Options:

    function getPic()
    {
        if (! $this->pic)
            throw exUnexpectedValue::paramIsRequired('pic');

        if (! $this->pic instanceof UploadedFile)
            throw new exUnexpectedValue('pic must be uploaded file.');

        if ($this->pic->getClientMediaType() !== 'image/jpeg')
            throw new exUnexpectedValue('Only Jpeg Files Is Allowed!');

        return $this->pic;
    }

    function getAsPrimary()
    {
        return (
            (is_bool($this->asPrimary)) ? $this->asPrimary : filter_var($this->asPrimary, FILTER_VALIDATE_BOOLEAN)
        );
    }
}
