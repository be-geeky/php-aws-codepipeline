<% if (products.count == 0 && categories.count == 0  && cms.count == 0 && suggests.count == 0) { %>
  <li>
    <span>No Result</span>
  </li>
<% } else if (products.count == 0 && categories.count == 0 && cms.count == 0 && suggests.enabled == 1 && suggests.count != 0) { %>
  <li class="qs-section only-suggest">
    <span class="qs-option-name">Do you mean?</span>
  </li>
  <% _.each(suggests.docs, function(doc) { %>
    <li role="option" class="qs-option">
      <a href="<%= _.getSearchUrl() + '?q=' + doc.text %>">
        <div class="info">
          <span class="name"><%= doc.text %>
          <% if (doc.count > 0) { %>
            <span class='count'><%= doc.count %></span>
          <% } %>
          </span>
        </div>
        <div class="clearer"></div>
      </a>
    </li>
  <% }); %>
<% } else { %>
  <div class="left">
    <!-- suggestions -->
    <% if (suggests.count > 0 && suggests.enabled == 1) { %>
      <div class="block-suggests">
        <li class="qs-section">
          <span class="qs-option-name">Do you mean?</span>
        </li>
        <% _.each(suggests.docs, function(doc) { %>
          <li role="option" class="qs-option">
            <a href="<%= _.getSearchUrl() + '?q=' + doc.text %>">
              <div class="info">
                <span class="name"><%= doc.text %>
                  <% if (doc.count > 0) { %>
                  <span class='count'><%= doc.count %></span>
                  <% } %>
                </span>
              </div>
              <div class="clearer"></div>
            </a>
          </li>
        <% }); %>
      </div>
    <% } %>
    <!-- categories -->
    <% if (categories.enabled == 1) { %>
    <div class="block-categories <% if (categories.count == 0) { %>no-result<% } %>">
        <li class="qs-section">
          <span class="qs-option-name">Categories</span>
          <% if (categories.count > 0) { %>
          	<span aria-hidden="true" class="amount"><%- categories.count %></span>
          <% } %>
        </li>
        <% if (categories.count == 0) { %>
          <span class='no-result'>No result</span>
        <% } %>
        <% _.each(categories.docs, function(doc) { %>
          <li role="option" class="qs-option">
            <a href="<%- doc.url %>">
              <div class="info">
                <span class="name"><%= doc.path %></span>
              </div>
              <div class="clearer"><!-- --></div>
            </a>
          </li>
        <% }); %>
      </div>
    <% } %>
    <!-- cms -->
    <% if (cms.enabled == 1) { %>
      <div class="block-cms <% if (cms.count == 0) { %>no-result<% } %>">
        <li class="qs-section">
          <span class="qs-option-name">Pages</span>
          <% if (cms.count > 0) { %>
          	<span aria-hidden="true" class="amount"><%- cms.count %></span>
          <% } %>
        </li>
        <% if (cms.count == 0) { %>
          <span class='no-result'>No result</span>
        <% } %>
        <% _.each(cms.docs, function(doc) { %>
          <li role="option" class="qs-option">
            <a href="<%= _.getBaseUrl() + doc.identifier %>">
              <div class="info">
                <span class="name"><%= doc.title %></span>
              </div>
              <div class="clearer"><!-- --></div>
            </a>
          </li>
        <% }); %>
      </div>
    <% } %>
  </div>
  <div class="sep"/>
  <div class="right">
    <!-- products -->
    <% if (products.enabled == 1) { %>
      <div class="block-products <% if (products.count == 0) { %>no-result<% } %>">
        <li class="qs-section products">
          <span class="qs-option-name">Products</span>
          <% if (products.count > 0) { %>
          	<span aria-hidden="true" class="amount"><%- products.count %></span>
          <% } %>
        </li>
        <% if (products.count == 0) { %>
        	<span class='no-result'>No result</span>
        <% } %>
        <% _.each(products.docs, function(doc) { %>
          <li role="option" class="qs-option product">
            <a href="<% if (typeof doc.shortest_url != "undefined") { %><%- doc.shortest_url %><% } else { %><%- doc.url %><% } %>">
              <% if (doc.image) { %>
              	<img src="<%- doc.image %>" alt="">
              <% } %>
              <div class="info">
                <span class="name"><%= doc.name %></span>
                <span class="category">in <%= doc.category %></span>
                <span class="price">
                  <div class="price-box">
                    <% if (doc.type_id == 'bundle') { %>
                      <p class="price-from">
                        <span class="price-label">From:&nbsp;</span>
                        <span class="price"><%= _.getFormattedPrice(doc.prices.min_price, doc.tax_class_id) %></span>
                      </p>
                      <p class="price-to">
                        <span class="price-label">To:&nbsp;</span>
                        <span class="price"><%= _.getFormattedPrice(doc.prices.max_price, doc.tax_class_id) %></span>
                      </p>
                    <% } else if (doc.type_id == 'grouped') { %>
                      <p class="minimal-price">
                        <span class="price-label">Starting at:&nbsp;</span>
                        <span class="price"><%= _.getFormattedPrice(doc.prices.min_price, doc.tax_class_id) %></span>
                      </p>
                    <% } else { %>
                      <% if (doc.prices.final_price < doc.prices.price) { %>
                        <p class="old-price">
                          <span class="price"><%= _.getFormattedPrice(doc.prices.price, doc.tax_class_id) %></span>
                        </p>
                        <p class="special-price">
                          <span class="price"><%= _.getFormattedPrice(doc.prices.final_price, doc.tax_class_id) %></span>
                        </p>
                      <% } else { %>
                        <span class="regular-price">
                          <span class="price"><%= _.getFormattedPrice(doc.prices.price, doc.tax_class_id) %></span>
                        </span>
                      <% } %>
                    <% } %>
                  </div>
                </span>
              </div>
              <div class="clearer"><!-- --></div>
            </a>
          </li>
        <% }); %>
      </div>
      <% if (products.count > 0) { %>
    	<span class="all-results"><a href="<%= _.getSearchUrl() + '?q=' + search_term %>">Show all results for <b><%= search_term %></b></a></span>
      <% } %>
    <% } %>
  </div>
<% } %>