package controllers

import javax.inject._

import models.Post
import play.api.libs.json.Json
import play.api.mvc._

case class PostsResponse(posts: List[Post], count: Int)

object PostsResponse{
  import play.api.libs.json._

  implicit val postsFormat: OFormat[PostsResponse] = Json.format[PostsResponse]
}

class PostController @Inject()(cc: ControllerComponents) extends AbstractController(cc) {

  def posts() = Action { implicit request: Request[AnyContent] =>
    val posts = List(Post("sdfksdjf VDM", None, None))
    Ok(Json.toJson(PostsResponse(posts, posts.length)))
  }
}