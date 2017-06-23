<?php
/**
 * MIT License
 *
 * Copyright (c) 2017, Pentagonal
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @author pentagonal <org@pentagonal.org>
 */

declare(strict_types=1);

namespace PentagonalProject\App\Rest\Util;

use Aura\Session\CsrfToken as CSRFToken;
use Aura\Session\Segment;
use Aura\Session\Session as AuraSession;
use Aura\Session\SessionFactory;
use InvalidArgumentException;

/**
 * Class Session
 * @package PentagonalProject\App\Rest\Util
 *
 * @see Segment::get()
 * @method mixed get(string $key, mixed $alt = null)
 *
 * @see Segment::set()
 * @method void  set(string $key, mixed $val)
 *
 * @see Segment::clear()
 * @method void  clear()
 *
 * @see Segment::setFlash()
 * @method void  setFlash(string $key, mixed $val)
 *
 * @see Segment::getFlash()
 * @method mixed getFlash(string $key, mixed $alt = null)
 *
 * @see Segment::clearFlash()
 * @method void  clearFlash()
 *
 * @see Segment::getFlashNext()
 * @method mixed getFlashNext(string $key, mixed $alt = null)
 *
 * @see Segment::setFlashNow()
 * @method void  setFlashNow(string $key, mixed $val)
 *
 * @see Segment::clearFlashNow()
 * @method void  clearFlashNow()
 *
 * @see Segment::keepFlash()
 * @method void  keepFlash()
 */
class Session implements \ArrayAccess
{
    /**
     * @var AuraSession
     */
    protected $session;

    /**
     * @var string
     */
    protected $segmentName = '';

    /**
     * Session constructor.
     * @param AuraSession $session
     */
    public function __construct(AuraSession $session = null)
    {
        if (is_null($session)) {
            $session = (new SessionFactory)->newInstance($_COOKIE);
        }

        $this->session =& $session;
        $this->setSegmentName(__CLASS__);
    }

    /**
     * This Method also change Segments
     *
     * @param string $name
     */
    public function setSegmentName(string $name)
    {
        $this->segmentName = $name;
    }

    /**
     * Create Instance Session
     *
     * @param string|null                $name
     * @param AuraSession|null $session
     * @return Session
     * @throws InvalidArgumentException
     */
    public static function &createWithName($name = null, AuraSession $session = null) : Session
    {
        $session = ! is_null($session) ? new static : new static($session);
        if (is_null($name)) {
            return $session;
        }

        $session->setSegmentName($name);
        return $session;
    }

    /**
     * Get object Session
     *
     * @return AuraSession
     */
    public function &getSession() : AuraSession
    {
        return $this->session;
    }

    /**
     * Start Or Resume Session
     *
     * @return bool
     */
    public function startOrResume() : bool
    {
        if (!$this->session->isStarted()) {
            return $this->session->start();
        }

        return $this->session->resume();
    }

    /**
     * Get the Session segment
     *
     * @return Segment
     */
    public function getSegment() : Segment
    {
        $segment = $this->getSession()->getSegment($this->getSegmentName());
        return $segment;
    }

    /**
     * Get session stored Name Value
     *
     * @return string
     */
    public function getSegmentName() : string
    {
        return $this->segmentName;
    }

    /**
     * Get C.S.R.F from Aura session
     *
     * @return CSRFToken
     */
    public function getCSRFToken() : CSRFToken
    {
        return $this->getSession()->getCsrfToken();
    }

    /**
     * Getting C.S.R.F token values
     *
     * @return string
     */
    public function getCSRFTokenValue() : string
    {
        return $this->getCSRFToken()->getValue();
    }

    /**
     * validate token set
     *
     * @param string $value
     * @return bool
     */
    public function validateToken($value) : bool
    {
        if (!is_string($value)) {
            return false;
        }

        return $this->getCSRFToken()->isValid($value);
    }

    /**
     * @param string $keyName
     * @param mixed $value
     */
    public function flash($keyName, $value)
    {
        $this->setFlash($keyName, $value);
    }

    /**
     * @param string $keyName
     * @param mixed $value
     */
    public function flashNow($keyName, $value)
    {
        $this->setFlashNow($keyName, $value);
    }

    /**
     * Check whether Session is exists or not on segment
     *
     * @param string $keyName
     * @return bool
     */
    public function exist($keyName) : bool
    {
        # double check
        return
            $this->get($keyName, true) !== true
            && $this->get($keyName, false) !== false;
    }

    /**
     * Remove Session from segment
     *
     * @param string $keyName
     */
    public function remove($keyName)
    {
        if ($this->exist($keyName)) {
            unset($_SESSION[$this->getSegmentName()][$keyName]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset) : bool
    {
        return $this->exist($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * Magic Method Call for BackWards Compatibility
     *
     * @uses   Segment
     * @param  string $name
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        $return = call_user_func_array(
            [$this->getSegment(), $name],
            $arguments
        );

        return $return;
    }
}
