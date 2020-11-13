<?php
namespace ImageManipulation\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
	
class ImageFiles implements ServiceManagerAwareInterface {

	protected $serviceManager;
	protected $config;

	//Injeta o array com as configuração do Zuni
	public function __construct($config = array()) {
		if(!empty($config)) {
			$this->setConfig($config);
		} else {
			throw new \Exception('As configurações do Zuni não foram definidas');
		}
	}

	public function move($file, $upload = true) {
		// Novo nome do arquivo
		// $file['new_name'] = substr(md5(time()), 0, 4) . '_' . rand() . '.' . $file['extension'];
		if(!$file['filename'])
			$file['filename'] = $file['name'];

		$file['new_name'] = md5_file($file['tmp_name']) . '.' . $file['extension'];
		if(file_exists($this->getTmpDirImage($file['new_name'])))
			unlink($this->getTmpDirImage($file['new_name']));

		// Faz o upload
		if($upload) {
			$filter = new \Zend\Filter\File\RenameUpload($this->getTmpDirImage($file['new_name']));
			$filter->filter($file);

			// Se o arquivo é compactado, chama o método de descompactação
			if($this->compressedFile($file['extension'])) {
				return $this->decompressFile($file);
			}
		} else {
			rename($this->getTmpDirImage($file['filename']), $this->getTmpDirImage($file['new_name']));
		}

		return $this->prepareImageJson($file['new_name']);
	}

	public function moveDefinitiveDirectory($fileName) {
		$tempDir = $this->getTmpDirImage($fileName);
		$newDir = $this->getUploadDirImage($fileName);

		rename($tempDir, $newDir);
		return $newDir;
	}

	// Pegando a extensão do arquivo
	public function getFileExtension($file) {
		//return substr(strrchr($file, '.'), 1);
		$info = new \SplFileInfo($file);
		return $info->getExtension();
	}

	// Extensões válidas
	public function validExtensions($extension) {
		if(in_array(strtolower($extension), $this->getConfig('valid_extensions'))) {
			return true;
		}
		return false;
	}

	// Arquivos compactados válidos
	public function compressedFile($extension) {
		$validExtensions = array('zip'/*, 'gz', 'tar'*/);
		if(in_array($extension, $validExtensions)) {
			return true;
		}
		return false;
	}

	// Método para descompactar arquivo
	public function decompressFile($file) {
		// Caminho absoluto do arquivo enviado
		$compressedFile = $this->getConfig('upload_dir') . $file['new_name'];

		// Diretório onde será descompactado
		$decompressDir = str_replace('.' . $file['extension'], '', $compressedFile) . '/';
		mkdir($decompressDir, 0777);

		// Escolhendo o método de descompressão
		$adapter = 'Zip';
		// @TODO: Fazer um switch/case pras outras extensões quando ouver

		// Descompactando
		$filter = new \Zend\Filter\Decompress(array(
			'adapter' => $adapter,
			'options' => array(
				'target' => $decompressDir
			)
		));
		$decompressed = $filter->filter($compressedFile);

		// Procura por imagens (com extensão válida) dentro do arquivo descompactado usando buscando em até 1 nível de subdiretório
		$images = array();
		$directories = $this->globDirectories($decompressDir);
		foreach($directories as $dir) {
			foreach(glob($dir . '/*.{' . implode(',', $this->getConfig('valid_extensions')) . '}', GLOB_BRACE) as $file) {
				$file = array(
					'extension' => $this->getFileExtension($file),
					'filename' => substr($file, strpos($file, $this->getConfig('upload_dir')) + strlen($this->getConfig('upload_dir'))),
				);

				// Copia a imagem para dentro da pasta /tmp/ (fora da pasta que foi criada para descompactar o conteúdo do arquivo)
				$images[] = $this->move($file, false);
			}
		}

		// Retorna todas as imagens encontradas já no formato necessário
		return $images;
	}

	public function prepareFilesToDownload($images) {
		if(empty($images)) {
			throw new \Exception("Nenhum arquivo informado");
		}

		if(is_array($images)) {
			return $this->compressFiles($images);
		} elseif($images instanceof \Application\Entity\Image) {
			$tempFile = $this->getTmpDirImage($images->getFilename());
			if(copy($this->getUploadDirImage($images->getFilename()), $tempFile)) {
				return $tempFile;
			} else {
				throw new \Exception("Erro ao gerar arquivo");
			}
		} else {
			throw new \Exception("Deve ser informado uma imagem ou um album");
		}
	}

	/**
	 * Gerar um arquivo zip contendo um conjunto de imagens
	 * @param array $images
	 * @throws \Exception
	 * @return string
	 */
	public function compressFiles($images=array()) {
		$filename = substr(md5(time()), 0, 4) . '_' . rand() . '.zip';
		$zip = new \ZipArchive();
		$zip->open($this->getTmpDirImage($filename), \ZipArchive::CREATE);

		foreach ($images as $img) {
			$file = $this->getUploadDirImage($img->getFilename());

			if(!is_file($file)) {
				throw new \Exception("$file não é um arquivo");
			}
			$zip->addFile($file, $img->getFileName());
		}
		$zip->close();

		/* if(!is_file($this->getUploadUrlTemp() . $filename)) {
			throw new \Exception('Falha ao gerar arquivo');
		} */

		return $this->getUploadUrlTemp() . $filename;
	}

	// Preparando o Json com as imagens
	public function prepareImageJson($filename, $temp = true) {

		$upload_url = $temp ? $this->getConfig('upload_url_temp') : $this->getConfig('upload_url');

		return array(
			'src' => array(
				'filename' => $filename,
				'thumbnail' => $this->getConfig('thumbnail_url') . $upload_url . $filename,
				'highResolution' => $upload_url . $filename
			)
		);
	}

	// Procurar diretórios
	public function globDirectories($search) {
		$dir = rtrim($search, '/');
		while($dirs = glob($dir . '/*', GLOB_ONLYDIR)) {
			$dir .= '/*';
			if(!$directories) {
				$directories = $dirs;
			} else {
				$directories = array_merge($directories, $dirs);
			}
		}
		return $directories;
	}

	/**
	 * Gera o caminho onde a imagem será armazenada de forma permanente de acordo com o filename
	 * @param string $fileName
	 * @return string
	 */
	public function generateDirectoryByFilename($fileName) {
		$newDir = '';
		foreach (str_split(substr($fileName, 0, 4)) as $char) {
			$newDir.= $char . '/';
		}

		return $newDir;
	}

	/**
	 * Retorna o UploadUrl da imagem
	 */
	public function getUploadUrlImage($filename) {
		return $this->getConfig('upload_url') . $this->generateDirectoryByFilename($filename) . $filename;
	}

	/**
	 * Retorna o ThumbnailUrl da imagem
	 */
	public function getThumbnailUrlImage($filename) {
		return $this->getConfig('thumbnail_url') . $this->getUploadUrlImage($filename);
	}

	/**
	 * Retorna o UploadDir da imagem
	 */
	public function getUploadDirImage($filename) {
		return $this->getConfig('upload_dir') . $this->generateDirectoryByFilename($filename) . $filename;
	}

	/**
	 * Retorna o TmpDirImage da imagem
	 */
	public function getTmpDirImage($filename) {
		return $this->getConfig('upload_temp_dir') . $filename;
	}

	/**
	 * Retorna o upload_url_temp
	 */
	public function getUploadUrlTemp() {
		return $this->getConfig('upload_url_temp');
	}

	/**
	 * Retorna a dimensão de um arquivo de imagem no formato (width)x(height)
	 * @param string $filename
	 * @return string
	 */
	public function getImageSize($filename) {
		list($width, $height) = getimagesize($filename);
		return array(
			'width' => $width,
			'height' => $height
		);
		// return $width.'x'.$height;
	}

	/**
	 * Retorna os matadados da imagem
	 * @param string $filename
	 */
	public function getMetadaData($file) {
		return exif_read_data($file);
	}

	public function setDefaultMetada($filename) {
		$iptc = new \Iptc($filename);
		$iptc->removeAllTags();
		$iptc->set(\Iptc::COPYRIGHT_STRING, 'UFMG');
		$iptc->write();
	}

	public function remove($filename) {
		if(is_file($this->getUploadDirImage($filename))) {
			unlink($this->getUploadDirImage($filename));
		}
	}

	public function setServiceManager(ServiceManager $serviceManager) {
		$this->serviceManager = $serviceManager;
	}

	/**
	 * @return ServiceLocator
	 */
	public function getServiceManager() {
		return $this->serviceManager;
	}

	public function setService($service) {
		return $this->getServiceManager()->get($service);
	}

	public function setConfig($config) {
		$this->config = $config;
	}

	/**
	 *
	 * @param string $key
	 * @throws \Exception
	 */
	public function getConfig($key=null) {
		if(empty($key))
			return $this->config;

		if(key_exists($key, $this->config))
			return $this->config[$key];
		else {
			throw new \Exception('Parameter ' . $key . 'not exists');
		}
	}
}