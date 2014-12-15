<?php
namespace O2System\Core\Database;
    /**
     * O2System
     *
     * An open source application development framework for PHP 5.2.4 or newer
     *
     * This content is released under the MIT License (MIT)
     *
     * Copyright (c) 2014, PT. Lingkar Kreasi (Circle Creative).
     *
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and associated documentation files (the "Software"), to deal
     * in the Software without restriction, including without limitation the rights
     * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the Software is
     * furnished to do so, subject to the following conditions:
     *
     * The above copyright notice and this permission notice shall be included in
     * all copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
     * THE SOFTWARE.
     *
     * @package     O2System
     * @author      Steeven Andrian Salim
     * @copyright   Copyright (c) 2005 - 2014, PT. Lingkar Kreasi (Circle Creative).
     * @license     http://circle-creative.com/products/o2system/license.html
     * @license     http://opensource.org/licenses/MIT  MIT License
     * @link        http://circle-creative.com
     * @since       Version 2.0
     * @filesource
     */
// ------------------------------------------------------------------------
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Database Transaction Class
 *
 * @package     O2System
 * @subpackage  system/core
 * @category    Core Class
 * @author      Steeven Andrian Salim
 * @link        http://circle-creative.com/products/o2system/user-guide/core/database/transaction.html
 */
// ------------------------------------------------------------------------
class Trans extends \O2System\Core\Driver
{
    /**
     * Transaction flag
     *
     * @access protected
     * @var	bool
     */
    protected $_active = FALSE;

    /**
     * Initiates a transaction
     *
     * @access  public
     * @return	bool
     */
    public function begin()
    {
        if ($this->_active === FALSE)
        {
            $this->_active = $this->pdo->beginTransaction();
            return $this->_active;
        }
        return FALSE;
    }

    /**
     * Transaction status
     * Checks if inside a transaction
     *
     * @access  public
     * @return	bool
     */
    public function status()
    {
        return $this->_active = $this->pdo->inTransaction();
    }

    /**
     * Rolls back a transaction
     *
     * @access  public
     * @return	bool
     */
    public function rollback()
    {
        $this->pdo->rollback();
        $this->hasActiveTransaction = FALSE;
    }

    /**
     * Commit a transaction
     *
     * @access  public
     * @return	bool
     */
    public function commit()
    {
        $this->pdo->commit();
        $this->_active = FALSE;
    }
}

/* End of file Trans.php */
/* Location: ./system/core/Database/drivers/Trans.php */