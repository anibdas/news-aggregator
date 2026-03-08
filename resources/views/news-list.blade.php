<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>News Aggregator</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #121212; color: #e0e0e0; }
        .hero-section { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 3rem 0; margin-bottom: 2rem; border-radius: 0 0 1rem 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
        .filter-sidebar { background-color: #1e1e1e; padding: 1.5rem; border-radius: 0.8rem; height: 100%; top: 20px; position: sticky; }
        .spinner-container { display: none; text-align: center; padding: 2rem; }
        .pagination { justify-content: center; }
        .form-control, .form-select { background-color: #2c2c2c; border-color: #444; color: white; }
        .form-control:focus, .form-select:focus { background-color: #333; color: white; border-color: #3498db; box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(52,152,219,.6); }

        /* Table Styles */
        .table-responsive { background-color: #1e1e1e; border-radius: 0.8rem; padding: 1rem; }
        #articlesTable { margin-bottom: 0; }
        #articlesTable thead th { background-color: #2a5298; color: white; border: none; padding: 1rem; font-weight: 600; }
        #articlesTable tbody tr { border-bottom: 1px solid #2c2c2c; }
        #articlesTable tbody tr:hover { background-color: #252525; }
        #articlesTable tbody td { padding: 1rem; vertical-align: middle; border: none; }
        #articlesTable tbody td a { font-weight: 500; }
        #articlesTable tbody td a:hover { text-decoration: underline !important; }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #121212; }
        ::-webkit-scrollbar-thumb { background: #444; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #555; }
    </style>
</head>
<body>

<div class="hero-section text-center text-white">
    <div class="container">
        <h1 class="display-4 fw-bold">News Aggregator</h1>
    </div>
</div>

<div class="container">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="filter-sidebar shadow">
                <h5 class="mb-3 border-bottom pb-2">Filter & Search</h5>
                
                <form id="filterForm">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Search Keyword</label>
                        <input type="text" id="filterKeyword" class="form-control" placeholder="Search articles...">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small">Date</label>
                        <input type="date" id="filterDate" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small">Category</label>
                        <select id="filterCategory" class="form-select">
                            <option value="">All Categories</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small">Source</label>
                        <select id="filterSource" class="form-select">
                            <option value="">All Sources</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <button type="button" id="resetFilters" class="btn btn-outline-secondary btn-sm">Reset</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Articles Feed -->
        <div class="col-lg-9">
            <div class="spinner-container" id="loadingSpinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-dark table-hover table-striped" id="articlesTable">
                    <thead class="table-primary">
                        <tr>
                            <th style="width: 25%;">Title</th>
                            <th style="width: 30%;">Description</th>
                            <th style="width: 12%;">Author</th>
                            <th style="width: 10%;">Source</th>
                            <th style="width: 10%;">Category</th>
                            <th style="width: 13%;">Published Date</th>
                        </tr>
                    </thead>
                    <tbody id="articlesContainer">
                        <!-- Articles injected by jQuery -->
                    </tbody>
                </table>
            </div>

            <nav class="mt-4" aria-label="Page navigation">
                <ul class="pagination" id="pagination">
                    <!-- Pagination injected by jQuery -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        // App globals
        var currentPage = 1;
        
        // Load metadata (Dropdown options)
        function loadMetadata() {
            $.get('/api/metadata', function(data) {
                // Populate filters
                populateSelect('#filterCategory', data.categories, true);
                populateSelect('#filterSource', data.sources, true);
            });
        }
        
        function populateSelect(selector, items, isFilter) {
            var select = $(selector);
            if (!isFilter) select.empty();
            
            $.each(items, function(id, name) {
                // For filter dropdowns we use name as value, for prefs we use ID
                var val = isFilter ? name : id; 
                select.append($('<option></option>').val(val).text(name));
            });
        }
        
        // Fetch Articles
        function fetchArticles() {
            $('#loadingSpinner').show();
            $('#articlesContainer').empty();
            $('#pagination').empty();
            
            var params = {
                page: currentPage,
                keyword: $('#filterKeyword').val(),
                date: $('#filterDate').val(),
                category: $('#filterCategory').val(),
                source: $('#filterSource').val()
            };
            
            $.get('/api/articles', params, function(res) {
                $('#loadingSpinner').hide();
                
                if (res.data.length === 0) {
                    $('#articlesContainer').html('<tr><td colspan="6" class="text-center text-muted py-5"><h5>No articles found.</h5><p>Try adjusting your filters.</p></td></tr>');
                    return;
                }

                var articlesHtml = '';
                res.data.forEach(article => {
                    var category = article.category.name;
                    var source = article.source.name;
                    var author = article.author.name;
                    var date = new Date(article.published_at).toLocaleDateString();
                    var desc = article.description.substring(0, 150);
                    var title = article.title;

                    articlesHtml += `
                        <tr>
                            <td>
                                <a href="${article.url}" target="_blank" class="text-decoration-none text-info">
                                    ${title}
                                </a>
                            </td>
                            <td class="text-muted small">${desc}</td>
                            <td>${author}</td>
                            <td><span class="badge bg-warning text-dark">${source}</span></td>
                            <td><span class="badge bg-info">${category}</span></td>
                            <td class="text-muted small">${date}</td>
                        </tr>
                    `;
                });

                $('#articlesContainer').html(articlesHtml);
                renderPagination(res);
            }).fail(function() {
                $('#loadingSpinner').hide();
                $('#articlesContainer').html('<tr><td colspan="6" class="text-danger text-center py-4">Failed to load articles. Please run the data fetcher command.</td></tr>');
            });
        }
        
        function renderPagination(res) {
            var pagHtml = '';
            
            if (res.prev_page_url) {
                pagHtml += `<li class="page-item"><a class="page-link shadow-sm bg-dark text-white border-secondary" href="#" data-page="${res.current_page - 1}">Previous</a></li>`;
            } else {
                pagHtml += `<li class="page-item disabled"><a class="page-link shadow-sm bg-dark text-muted border-secondary">Previous</a></li>`;
            }
            
            pagHtml += `<li class="page-item disabled"><a class="page-link shadow-sm bg-secondary text-white border-secondary">Page ${res.current_page} of ${res.last_page}</a></li>`;
            
            if (res.next_page_url) {
                pagHtml += `<li class="page-item"><a class="page-link shadow-sm bg-dark text-white border-secondary" href="#" data-page="${res.current_page + 1}">Next</a></li>`;
            } else {
                pagHtml += `<li class="page-item disabled"><a class="page-link shadow-sm bg-dark text-muted border-secondary">Next</a></li>`;
            }
            
            $('#pagination').html(pagHtml);
        }
        
        $('#pagination').on('click', '.page-link', function(e) {
            e.preventDefault();
            var page = $(this).data('page');
            if (page) {
                currentPage = page;
                fetchArticles();
            }
        });
        
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            currentPage = 1;
            fetchArticles();
        });
        
        $('#resetFilters').click(function() {
            $('#filterForm')[0].reset();
            currentPage = 1;
            fetchArticles();
        });
        
        // Init
        loadMetadata();
        fetchArticles();
    });
</script>
</body>
</html>
