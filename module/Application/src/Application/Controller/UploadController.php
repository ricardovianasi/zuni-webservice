<?php
namespace Application\Controller;

class UploadController extends AbstractRestfulJsonController {

	public function create($data) {
		// Pegando o arquivo que foi enviado
		$file = $this->params()->fromFiles('file');
		$file['extension'] = $this->getImageFilesService()->getFileExtension($file['name']);
		if($file['error'] == 1) {
			$this->getJsonModel()->gallery = array(
				'error' => 'true',
				'message' => 'Erro durante o envio.'
			);
			return $this->getJsonModel();
		}
		// Se não é uma imagem válida ou um arquivo compactado válido
		if(!$this->getImageFilesService()->validExtensions($file['extension']) && !$this->getImageFilesService()->compressedFile($file['extension'])) {
			$this->getJsonModel()->gallery = array(
				'error' => 'true',
				'message' => 'Tipo de arquivo não suportado.'
			);
			return $this->getJsonModel();
		}
		// Faz upload
		$gallery = $this->getImageFilesService()->move($file);

		// Retornando a(s) imagem(s)
		$this->getJsonModel()->gallery = $gallery;
		return $this->getJsonModel();
	}
}