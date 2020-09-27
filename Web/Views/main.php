<h1>Рейтинг фильмов!</h1>
<div class="alert alert-info" role="alert">
    <h4>Категории:</h4>
    <ul id="rating_categories"></ul>
</div>
<h4>Дата рейтинга</h4>
<div class="form-group">
    <input type="date" id="date" name="date" class="form-control" onchange="setDate()">
</div>    
<div id="app_data">
    <table class="table table-sm">
        <thead class="thead-light">
            <tr>
                <th scope="col">№</th>
                <th scope="col" style="cursor: pointer;" onclick="changeSort('title')">Наименование</th>
                <th scope="col" style="cursor: pointer;" onclick="changeSort('year')">Год</th>
                <th scope="col" style="cursor: pointer;" onclick="changeSort('place')">Место</th>
                <th scope="col" style="cursor: pointer;" onclick="changeSort('date')">Дата рейтинга</th>
                <th scope="col" style="cursor: pointer;" onclick="changeSort('grade')">Расчетный балл</th>
                <th scope="col" style="cursor: pointer;" onclick="changeSort('voites')">Голосов</th>
                <th scope="col" style="cursor: pointer;" onclick="changeSort('average_grade')">Средний балл</th>
            </tr>
        </thead>
        <tbody id="films_table"></tbody>
    </table>
</div>
<div class="m-3">
    <button onclick="getFilms(getFilmsForCategory)" class="btn btn-primary ml-auto">Получить фильмы из категории</button>
    <!--div class="form-group">
        <input type="checkbox" id="withrating" name="withrating" checked class="form-check-input" onchange="setWithRating()">
        <label for="withrating" class="form-check-label">Получить с рейтингом</label>
    </div-->
</div>
<h4>Выберите категорию:</h4>
<div id="checkbox_div" class="form-group"></div>

<!-- Modal -->
<div class="modal fade bd-example-modal-lg" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Описание фильма</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="film_modal_body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
    let getAllFilmsURL = "api/all-films";
    let getFilmsForCategory = "api/films-for-categories";
    let getAllCategories = "api/all-categories";
    let Categories = [];
    let WithRating = true;
    let SortBy = {field: "place", direction: 1};
    let CurrentDate = "2020-09-27";
    let CurrentURL = null;
    let CategoriesDataArray = [];
    let FilmsDataArray = [];
    
    let CurrentFilm = {};

    function showModal(id) //, Title, Year, Description, Picture)
    {
        CurrentFilm = FilmsDataArray.find( Film => {
            return Film.id == id;
        } );

        let ModalBody = document.getElementById("film_modal_body");
        let TitleDiv = document.createElement("div");
        let DescrDiv = document.createElement("div");
        let ImgDiv = document.createElement("div");
        let Paragraph = document.createElement("p");
        let H1 = document.createElement("h1");
        let Img = document.createElement("img");
        H1.innerHTML = CurrentFilm.title;
        TitleDiv.appendChild(H1);
        Paragraph.innerHTML = CurrentFilm.description;
        DescrDiv.appendChild(Paragraph);
        Img.src = "Images/" + CurrentFilm.picture;
        Img.classList.add("img-fluid", "mw-100");
        ImgDiv.appendChild(Img);
        ModalBody.innerHTML = "";
        ModalBody.appendChild(TitleDiv);
        ModalBody.appendChild(DescrDiv);
        ModalBody.appendChild(ImgDiv);
        
        $("#exampleModal").modal('show');
    }

    function changeSort(Field)
    {
        if( SortBy.field == Field ) { SortBy.direction *= -1; }
        else { SortBy = {field: Field, direction: 1}; }
        
        if(CurrentURL)
        {
            getFilms(CurrentURL);
        }
    }
    
    function setDate()
    {
        let DateInput = document.getElementById("date");
        CurrentDate = DateInput.value;
    }
    
//    function setWithRating()
//    {
//        let CheckBox = document.getElementById("withrating");
//        WithRating = CheckBox.checked;
//        if(!WithRating)
//        {
//            SortBy = {field: "title", direction: 1};
//        }
//    }

    async function getData(FromUrl, RequestData)
    {
        let res = await fetch(FromUrl,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json;charset=utf-8'
                    },
                    body: JSON.stringify(RequestData)
                }
        );        
        let Data = await res.json();
        return Data;
    }

    function getFilms(FromUrl)
    {
//        setWithRating();
        let DataDiv = document.getElementById("films_table");
        
        getData(FromUrl, {
            categories: Categories, 
            withrating: WithRating,
            sortby: SortBy,
            date: CurrentDate
        }).then(Films => {
            CurrentURL = FromUrl;
            DataDiv.innerHTML = "";
            if(!Films) { alert("Нет данных Films!"); }
            renderFilms(Films, DataDiv);
            FilmsDataArray = Films;
        })
        .catch( Error => {
            console.log(Error);
            alert("Данные фильмов получить не удалось!");
        }); // renderFilms(Films, DataDiv) );
    }

    function getCategories()
    {
        let DataDiv = document.getElementById("checkbox_div");
        
        getData(getAllCategories, Categories)
        .then(Categories => { 
            DataDiv.innerHTML = "";
            if(!Categories) { alert("Нет данных Categories!"); }
            renderCategories(Categories, DataDiv);
            CategoriesDataArray = Categories;
        })
        .catch( Error => {
            console.log(Error);
            alert("Данные категорий получить не удалось!");
        });
    }

    function renderFilms(Films, To)
    {
        if (!Films) {
            alert("Ничего нет!");
            return;
        }
        let Div, Th, Td, Tr, Text, Bold;
        let Attr;
        Films.forEach(Film => {

            Tr = document.createElement('tr');
//                <th scope="col">№</th>
            Th = document.createElement('th');
            Th.scope = "row";
            Text = document.createTextNode(Film.id);
            Th.appendChild(Text);
            Tr.appendChild(Th);

//                <th scope="col">Наименование</th>
            Td = document.createElement('td');
            Td.style.cursor = "pointer";
            Div = document.createElement('div');
            Bold = document.createElement('b');
            Bold.innerHTML = Film.title;
            Div.appendChild(Bold);
            Attr = document.createAttribute("onclick");
            Attr.value = "showModal(" + Film.id + ")";
//            Text = document.createTextNode(Film.title);
//            Div.appendChild(Text);
            Td.setAttributeNode(Attr);
            Td.appendChild(Div);
            Tr.appendChild(Td);

//                <th scope="col">Год</th>
            Td = document.createElement('td');
            Text = document.createTextNode(Film.year);
            Td.appendChild(Text);
            Tr.appendChild(Td);

//                <th scope="col">Место</th>
            Td = document.createElement('td');
            Text = document.createTextNode(Film.Ratings[0].place);
            Td.appendChild(Text);
            Tr.appendChild(Td);
//                <th scope="col">Дата рейтинга</th>
            Td = document.createElement('td');
            Text = document.createTextNode(Film.Ratings[0].date);
            Td.appendChild(Text);
            Tr.appendChild(Td);
//                <th scope="col">Расчетный балл</th>
            Td = document.createElement('td');
            Text = document.createTextNode(Film.Ratings[0].grade);
            Td.appendChild(Text);
            Tr.appendChild(Td);
//                <th scope="col">Голосов</th>
            Td = document.createElement('td');
            Text = document.createTextNode(Film.Ratings[0].voites);
            Td.appendChild(Text);
            Tr.appendChild(Td);
//                <th scope="col">Средний балл</th>
            Td = document.createElement('td');
            Text = document.createTextNode(Film.Ratings[0].average_grade);
            Td.appendChild(Text);
            Tr.appendChild(Td);

            To.appendChild(Tr);
        });
    }

    function renderCategories(Categories, To)
    {
        if (!Categories) {
            alert("Ничего нет!");
            return;
        }

        let CheckBox, Div, Div1, Div2, Div3, Label, Text;
        Categories.forEach(Category => {

            Div1 = document.createElement('div');
            Div1.classList.add("input-group", "mb-2");
            Div2 = document.createElement('div');
            Div2.classList.add("input-group-prepend");
            Div3 = document.createElement('div');
            Div3.classList.add("input-group-text");
            
            CheckBox = document.createElement('input');
            //CheckBox.classList.add("form-check-input");
            CheckBox.id = "CheckBox" + Category.id;
            CheckBox.type = "checkbox";
            CheckBox.name = "categories";
            CheckBox.value = Category.id;
            CheckBox.checked = false;
            CheckBox.onchange = changeCheckBox;

            Div = document.createElement('div');
            Label = document.createElement('label');
            Label.classList.add("form-control");
            Label.htmlFor = CheckBox.id;
            Text = document.createTextNode(Category.title);
            Label.appendChild(Text);
            Div3.appendChild(CheckBox);
            Div2.appendChild(Div3);
            Div1.appendChild(Div2);
            Div1.appendChild(Label);

            To.appendChild(Div1);
        });
    }

    function changeCheckBox(e)
    {
        Categories[e.target.value] = e.target.checked;
        let Ul = document.getElementById("rating_categories");
        let Li;
        let i=0;
        let tmp;
        Ul.innerHTML = "";
        for(i=0;i<Categories.length;i++)
        {
            if(Categories[i])
            {
                tmp = CategoriesDataArray.find( Category => {
                    return Category.id == i;
                } );
                if(!tmp) { continue; }
                Li = document.createElement("li");
                Li.innerHTML = tmp.title;
                Ul.appendChild(Li);
            }
        }
        
    }
    getCategories();

    let DateInput = document.getElementById("date");
    let DateObject = new Date();
    let Month = DateObject.getMonth()+1;
    if(Month<10) Month = '0' + Month;
    let Day = DateObject.getDate();
    if(Day<10) Day = '0' + Month;

    DateInput.value = DateObject.getFullYear() + "-" + Month + "-" + Day;

</script>