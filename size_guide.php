<?php
ob_start();
$title_page = "Size Guide";
?>

<section class="container py-5">
    <h1 class="fw-bold mb-4">Size Guide</h1>
    
    <div class="table-responsive">
        <table class="table table-bordered text-center">
            <thead class="table-light">
                <tr>
                    <th>Size</th>
                    <th>Chest (inches)</th>
                    <th>Waist (inches)</th>
                    <th>Hips (inches)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>S</td>
                    <td>34-36</td>
                    <td>28-30</td>
                    <td>34-36</td>
                </tr>
                <tr>
                    <td>M</td>
                    <td>38-40</td>
                    <td>32-34</td>
                    <td>38-40</td>
                </tr>
                <tr>
                    <td>L</td>
                    <td>42-44</td>
                    <td>36-38</td>
                    <td>42-44</td>
                </tr>
                <tr>
                    <td>XL</td>
                    <td>46-48</td>
                    <td>40-42</td>
                    <td>46-48</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<?php
$content = ob_get_clean();
include_once("layout.php");
?>
