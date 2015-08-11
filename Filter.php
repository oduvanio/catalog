<?php
namespace itlife\catalog;

/**
 * Класс обеспечивает негарантированнео хранение параметров в экстрокороткой строке из 2 символов
 * Это работает за счёт сохранения объекта данных в 2х символах, со временем данные этих 2х символов буду заменены, но нам важно только короткая память
 * возможность обменяться ссылками, кнопки вперёд назад.
 * так называемая приставка окружения env содержит в себе зашифрованную часть (2 символа) и изменение к зашифрованной части
 * например ?Каталог:aa содержит защифрованную часть aa которая на сервере развернётся в объект данных {page:1,prod:"Арсенал"}
 * например aa:{"page":2} - зашифрованная часть aa объединится с изменениями и получится {page:2,prod:"Арсенал"}
 * объект данных {page:2,prod:"Арсенал"} зашифруется в новую комбинацию xx и дальнейшие ссылки уже относительно этой пары символов
 * $filter=new Filter($str); //$str содержит приставку
 * $val=$filter->getVal();
 * $fd=$filter->getData();
 * Проверить $fd
 * $filter->setData($fd);
 * $mark=$filter->getMark(); //приставка для следующего $str
 */
class Filter
{
	private $sym = ':';
	//Если метка есть а даных нет считаем что метка устарела.
	//Недопускаем ситуации что метка появилась до появления привязанных к ней данных
	public $isold = true; //Если метка устарела значит какие данные мы не знаем...
	public $isadd = false;
	public $isnewval = false;
	public $old=array();
	public $add=array();

	private $mark = '';
	private $data = array();
	private $retmark = '';

	private $len = 2;
	private $prefix = 'env-';
	public function getMark()
	{
		return $this->retmark;
	}
	public function getVal()
	{
		return $this->val;
	}
	public function getData($newdata = null)
	{
		return $this->data;
	}
	public function setData($newdata)
	{
		if (!is_array($newdata)) {
			$newdata=array();
		}
		foreach ($newdata as $k => $v) {
			if (is_null($v)) {
				unset($newdata[$k]); //Удаление
			}
		}
		$this->data=$newdata;
		if (!$newdata) {
			$this->mark='';
		} else {
			$this->mark=$this->makeMark($this->data);
		}
		$this->retmark=$this->sym.$this->mark.$this->sym;
		
	}
	public function __construct($getval)
	{
		$getval=infra_toutf(strip_tags($getval));
		$this->getval=$getval;

		$r=explode($this->sym, $getval);
		$this->val=infra_forFS(array_shift($r));
		$this->mark=array_shift($r);

		if (!$this->mark) {
			$this->mark='';
		} else {
			$data=infra_mem_get($this->prefix.$this->mark);
			if (!$data || !is_array($data['data'])) {
				$this->mark='';
				$this->isold=false;
			} else {
				if ($this->val!=$data['val']) {
					$this->isnewval=true;
				}
				$this->old=$data['data'];
			}
		}
		$add=implode($this->sym, $r);


		$regex = '/(?<!")([a-zA-Z0-9_]+)(?!")(?=:)/i';
		$add=preg_replace($regex, '"$1"', $add);
		$add=infra_json_decode('{'.$add.'}', true);
		if (is_array($add)&&$add) {
			$this->add=$add;
			$this->isadd=true;
			$this->data=array_merge($this->old, $add);
		} else {
			$this->data=$this->old;
		}
	}
	private function makeMark($data)
	{
		ksort($data);
		$key=md5(serialize($data));
		$mark=substr($key, 0, $this->len);
		$that=$this;
		infra_once($this->prefix.$mark, function () use ($data, $that, $mark) {
			infra_mem_set($that->prefix.$mark, array(
				'val'=>$that->val,
				'data'=>$data));
		});
		return $mark;
	}
}
