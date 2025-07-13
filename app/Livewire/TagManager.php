<?php

namespace App\Livewire;

use App\Models\Tag;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class TagManager extends Component
{
    use WithPagination;
    
    public $showAddForm = false;
    public $name = '';
    public $color = '#3b82f6'; // Default blue color
    public $editingTagId = null;
    public $message = null;
    public $messageType = null;
    
    public function toggleAddForm()
    {
        $this->showAddForm = !$this->showAddForm;
        if (!$this->showAddForm) {
            $this->resetForm();
        }
    }
    
    public function resetForm()
    {
        $this->name = '';
        $this->color = '#3b82f6';
        $this->editingTagId = null;
    }
    
    public function saveTag()
    {
        $this->validate([
            'name' => 'required|min:2|max:50',
            'color' => 'required|regex:/^#[a-f0-9]{6}$/i',
        ]);
        
        try {
            if ($this->editingTagId) {
                $tag = Tag::where('user_id', Auth::id())->findOrFail($this->editingTagId);
                $tag->update([
                    'name' => $this->name,
                    'color' => $this->color,
                ]);
                $this->message = 'Tag updated successfully!';
            } else {
                // Check if tag with same name already exists for this user
                $existingTag = Tag::where('user_id', Auth::id())
                    ->where('name', $this->name)
                    ->first();
                    
                if ($existingTag) {
                    $this->message = 'A tag with this name already exists.';
                    $this->messageType = 'error';
                    return;
                }
                
                Tag::create([
                    'user_id' => Auth::id(),
                    'name' => $this->name,
                    'slug' => Str::slug($this->name),
                    'color' => $this->color,
                ]);
                $this->message = 'Tag created successfully!';
            }
            
            $this->messageType = 'success';
            $this->resetForm();
            $this->showAddForm = false;
            $this->dispatch('tag-updated');
        } catch (\Exception $e) {
            $this->message = 'Error saving tag: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }
    
    public function editTag($tagId)
    {
        $tag = Tag::where('user_id', Auth::id())->findOrFail($tagId);
        $this->editingTagId = $tag->id;
        $this->name = $tag->name;
        $this->color = $tag->color;
        $this->showAddForm = true;
    }
    
    public function deleteTag($tagId)
    {
        try {
            $tag = Tag::where('user_id', Auth::id())->findOrFail($tagId);
            $tag->delete();
            
            $this->message = 'Tag deleted successfully!';
            $this->messageType = 'success';
            
            $this->dispatch('tag-updated');
        } catch (\Exception $e) {
            $this->message = 'Error deleting tag: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }
    
    public function clearMessage()
    {
        $this->message = null;
        $this->messageType = null;
    }
    
    public function render()
    {
        $tags = Tag::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();
            
        return view('livewire.tag-manager', [
            'tags' => $tags
        ]);
    }
}
