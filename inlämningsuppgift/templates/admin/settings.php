<?php $pageTitle = 'Admin – Settings'; ?>
<?php require __DIR__ . '/../admin_layout.php'; ?>

<h1>Settings</h1>

<div class="card">
    <table>
        <div class="form-group">   
            <label>Color mode</label>
            <select name="color_mode">
                <option value="light">Light</option>
                <option value="red">Red</option>
                <option value="auto" selected>Dark (Default)</option>
            </select>
        </div>
    </table>
</div>

<div class="card">
    <table>
        <tbody>
            <tr>
                <label>Seed Data settings</label>
                <td><strong><button>Seed Data</button></strong></td>
            </tr>
            <tr>
                <td><strong><button>Remove Data</button></strong></td>
            </tr>    
        </tbody>
    </table>
</div>

<div class="card">
    <table>
        <tbody>
            <label>Database settings</label>
            <tr>
                <td><strong><button>Create Database</button></strong></td>
            </tr>
            <tr>
                <td><strong><button>Remove Database</button></strong></td>
            </tr>    
        </tbody>
    </table>
</div>

            