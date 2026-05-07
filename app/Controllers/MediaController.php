<?php

class MediaController extends Controller {
    private int $storeId;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireStore();
        $this->storeId = (int)$this->currentStore['id'];
    }

    public function index(): void {
        $media = $this->paginate("SELECT * FROM media WHERE store_id = ? ORDER BY created_at DESC", [$this->storeId], 30);
        $this->view('admin.media.index', compact('media'));
    }

    public function upload(): void {
        CSRF::validateOrFail();
        if (!$this->request->hasFile('file')) {
            $this->json(['error' => 'No file provided'], 400);
            return;
        }
        $file     = $this->request->file('file');
        $uploader = (new Upload())->disk('public')->to("uploads/media/{$this->storeId}")->maxSize(10240);
        $u        = $uploader->handle($file);
        $id = Database::insert('media', [
            'store_id'  => $this->storeId,
            'user_id'   => $this->currentUser['id'],
            'name'      => $u['original'],
            'file_name' => $u['filename'],
            'mime_type' => $u['mime'],
            'size'      => $u['size'],
            'path'      => $u['path'],
            'url'       => $u['url'],
        ]);
        $this->json(['success' => true, 'id' => $id, 'url' => $u['url'], 'thumbnail' => $u['thumbnail']]);
    }

    public function destroy(int $id): void {
        $m = Database::fetch("SELECT * FROM media WHERE id = ? AND store_id = ?", [$id, $this->storeId]);
        if (!$m) $this->abort(404);
        Upload::delete($m['url']);
        Database::delete('media', 'id = ?', [$id]);
        $this->json(['success' => true]);
    }
}
