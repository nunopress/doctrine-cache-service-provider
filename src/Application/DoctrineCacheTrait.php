<?php

namespace NunoPress\Silex\Application;

/**
 * Class DoctrineCacheTrait
 * @package NunoPress\Silex\Application
 */
trait DoctrineCacheTrait
{
	/**
	 * @param string $id
	 * @return mixed
	 */
	public function fetchCache($id)
	{
		return $this['cache']->fetch($id);
	}

	/**
	 * @param string $id
	 * @return bool
	 */
	public function containsCache($id)
	{
		return $this['cache']->contains($id);
	}

    /**
     * @param string $id
     * @param mixed $data
     * @param int|bool $lifeTime
     * @return bool
     */
    public function saveCache($id, $data, $lifeTime = false)
    {
        return $this['cache']->save($id, $data, $lifeTime);
    }

    /**
	 * @param string $id
	 * @return bool
	 */
	public function deleteCache($id)
	{
		return $this['cache']->delete($id);
	}

    /**
     * @param string $profile
     * @return \Doctrine\Common\Cache\Cache
     */
	public function cache($profile)
    {
        return $this['cache.stores'][$profile];
    }
}