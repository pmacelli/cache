<?php namespace Comodojo\Cache\Traits;

/**
 *
 *
 * @package     Comodojo Spare Parts
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @license     MIT
 *
 * LICENSE:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

trait GenericManagerTrait {

    public function genericAddProvider($provider, $weight = 0) {

        $this->stack->add($provider, $weight);

        return $this;

    }

    public function removeProvider($id) {

        return $this->stack->remove($id);

    }

    public function getProvider($id) {

        return $this->stack->get($id);

    }

    public function getProviders($enabled = false) {

        return $this->stack->getAll($enabled);

    }

    public function getSelectedProvider() {

        return $this->selected === null ? $this->void : $this->selected;

    }

    public function clear() {

        if ( $this->align_cache === false && $this->pick_mode < 5) {
            return $this->selectProvider()->clear();
        }

        $result = [];

        foreach ($this->stack as $provider) {

            $result[] = $provider[0]->clear();

        }

        return !in_array(false, $result);

    }

    public function setNamespace($namespace = null) {

        foreach ($this->stack->getAll(false) as $provider) {
            $provider->setNamespace($namespace);
        }

        $this->namespace = empty($namespace) ? "GLOBAL" : $namespace;

        return $this;

    }

    public function clearNamespace() {

        if ( $this->align_cache === false && $this->pick_mode < 5) {
            return $this->selectProvider()->clearNamespace();
        }

        $result = [];

        foreach ($this->stack->getAll() as $provider) {
            $result[] = $provider->clearNamespace();
        }

        return !in_array(false, $result);

    }

    public function getStats() {

        $stats = [];

        foreach ($this->stack->getAll(false) as $provider) {
            $stats[] = $provider->getStats();
        }

        return $stats;

    }

    protected function selectProvider() {

        switch ($this->pick_mode) {

            case 1:
                $provider = $this->stack->getFirstProvider();
                break;
            case 2:
                $provider = $this->stack->getLastProvider();
                break;
            case 3:
                $provider = $this->stack->getRandomProvider();
                break;
            case 4:
                $provider = $this->stack->getHeavyProvider();
                break;

        }

        $this->selected = $provider == null ? $this->void : $provider;

        return $this->selected;

    }

}
